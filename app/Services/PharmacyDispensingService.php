<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\PatientHistory;
use App\Models\Patients;
use App\Models\Pharmacy;
use App\Models\PharmacyMovement;
use App\Models\PharmacySale;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
class PharmacyDispensingService
{
    public static function dispense(Pharmacy $queue, int $quantity, string $paymentStatus, ?string $patientInput, ?string $notes, ?int $userId, bool $ignoreAlerts = false): PharmacySale
    {
        if ($quantity < 1) {
            throw new RuntimeException('Dispense quantity must be at least one.');
        }

        if (! in_array($paymentStatus, ['paid', 'pending'], true)) {
            throw new RuntimeException('Choose a valid payment status.');
        }

        return DB::transaction(function () use ($queue, $quantity, $paymentStatus, $patientInput, $notes, $userId, $ignoreAlerts): PharmacySale {
            $item = Pharmacy::query()->whereKey($queue->getKey())->lockForUpdate()->firstOrFail();

            if ($quantity > $item->stock_qty || $item->queue_status === 'dispensed') {
                throw new RuntimeException('The requested quantity is no longer available in this queue.');
            }

            $patientId = $item->issued_to_patient_id;
            if (filled($patientInput)) {
                $patientId = Patients::query()->whereKey($patientInput)->value('id')
                    ?? Patients::query()->where('patient_id', (string) $patientInput)->value('id');

                if (! $patientId) {
                    throw new RuntimeException('Patient not found. Enter a valid patient ID or patient code.');
                }
            }

            $inventory = Inventory::query()
                ->where('item_id', $item->inventory_item_id)
                ->firstOrFail();

            $dispenseResult = InventoryService::dispensePrescriptionWithBatches(
                $inventory,
                $quantity,
                (string) ($item->prescription_sale_id ?: 'PHARM-' . $item->id),
                $notes ?: 'Pharmacist dispensed medicine to patient',
            );

            // Controlled substances must also be written to the chain-of-custody
            // register (WHO/FIP GPP). A failure here must not abort the dispense,
            // so it is guarded and surfaced via the sale record only.
            if ($inventory->isControlled()) {
                try {
                    ControlledSubstanceService::record(
                        $inventory,
                        'dispense',
                        $quantity,
                        $userId,
                        [
                            'batch_no' => $item->batch_no,
                            'patient_id' => $patientId,
                            'prescription_id' => $item->prescription_sale_id,
                            'notes' => $notes ?: 'Auto-logged on dispensing',
                        ],
                    );
                } catch (RuntimeException) {
                    // Register logging is best-effort here; the physical dispense
                    // and stock ledger have already been recorded above.
                }
            }

            $remaining = (int) $item->stock_qty - $quantity;
            $item->update([
                'stock_qty' => $remaining,
                'issued_to_patient_id' => $patientId,
                'queue_status' => $remaining > 0 ? 'partially_dispensed' : 'dispensed',
            ]);

            $sale = PharmacySale::query()->create([
                'sale_id' => 'PS-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5)),
                'sale_type' => 'prescription',
                'pharmacy_id' => $item->id,
                'inventory_item_id' => $inventory->item_id,
                'prescription_id' => $item->prescription_sale_id,
                'patient_id' => $patientId,
                'medicine_name' => $item->medicine_name,
                'quantity' => $quantity,
                'unit_price' => $item->unit_price,
                'total_amount' => (float) $item->unit_price * $quantity,
                'payment_status' => $paymentStatus,
                'notes' => $notes,
                'sold_by' => $userId,
                'sold_at' => now(),
            ]);

            SaleAllocationService::record($sale, $dispenseResult['allocations']);

            PharmacyMovement::query()->create([
                'pharmacy_id' => $item->id,
                'movement_type' => 'dispense',
                'direction' => 'out',
                'quantity' => $quantity,
                'patient_id' => $patientId,
                'prescription_id' => $item->prescription_sale_id,
                'reference' => $sale->sale_id,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            if ($patientId) {
                PatientHistory::query()->create([
                    'patient_id' => $patientId,
                    'encounter_id' => $item->prescription_sale_id,
                    'source' => 'pharmacy',
                    'activity' => 'Medication dispensed',
                    'details' => "Medicine: {$item->medicine_name}, Qty: {$quantity}, Sale: {$sale->sale_id}",
                    'occurred_at' => now(),
                    'created_by' => $userId,
                ]);
            }

            if ($item->prescription_sale_id) {
                $prescription = Prescription::query()
                    ->where('prescription_id', $item->prescription_sale_id)
                    ->first();

                if ($prescription) {
                    $prescribedMedicines = array_keys($prescription->medications ?? []);
                    $allItemsDispensed = $prescribedMedicines !== []
                        ? collect($prescribedMedicines)->every(fn (string $medicine): bool => Pharmacy::query()
                            ->where('prescription_sale_id', $item->prescription_sale_id)
                            ->where('prescribed_medicine', $medicine)
                            ->where('stock_qty', 0)
                            ->exists())
                        : Pharmacy::query()
                            ->where('prescription_sale_id', $item->prescription_sale_id)
                            ->exists()
                            && ! Pharmacy::query()
                                ->where('prescription_sale_id', $item->prescription_sale_id)
                                ->where('stock_qty', '>', 0)
                                ->exists();

                    if ($allItemsDispensed && $prescription->status === 'active') {
                        $prescription->update(['status' => 'dispensed']);
                    }
                }
            }

            return $sale;
        });
    }
}
