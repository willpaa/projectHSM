<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\PharmacyBatch;
use App\Models\PharmacyBatchMovement;
use App\Models\Pharmacy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public static function purchaseOrder(
        Inventory $inventory,
        int $quantity,
        string $referenceId,
        ?string $notes = null,
        ?string $batchNo = null,
        ?string $expiryDate = null,
        ?float $purchasePrice = null,
        ?string $supplierId = null,
    ): InventoryTransaction {
        return static::apply(
            $inventory,
            abs($quantity),
            'purchase',
            $referenceId,
            $notes,
            batch: [
                'batch_no' => $batchNo ?: 'PO-' . $referenceId,
                'expiry_date' => $expiryDate,
                'purchase_price' => $purchasePrice ?? (float) $inventory->purchase_price,
                'supplier_id' => $supplierId ?? $inventory->supplier_id,
            ],
        );
    }

    public static function saleInvoice(Inventory $inventory, int $quantity, string $referenceId, ?string $notes = null): InventoryTransaction
    {
        return static::apply($inventory, -abs($quantity), 'sale', $referenceId, $notes);
    }

    public static function dispensePrescription(Inventory $inventory, int $quantity, string $referenceId, ?string $notes = null): InventoryTransaction
    {
        return static::apply($inventory, -abs($quantity), 'dispense', $referenceId, $notes);
    }

    public static function removeExpired(Inventory $inventory, int $quantity, ?string $notes = null): InventoryTransaction
    {
        return static::apply($inventory, -abs($quantity), 'expired', 'EXP-' . now()->format('YmdHis'), $notes);
    }

    public static function adjustStock(Inventory $inventory, int $quantity, ?string $notes = null, ?string $referenceId = null): InventoryTransaction
    {
        if ($quantity === 0) {
            throw new RuntimeException('Adjustment quantity cannot be zero.');
        }

        return static::apply(
            $inventory,
            $quantity,
            'adjustment',
            $referenceId ?: 'ADJ-' . now()->format('YmdHis'),
            $notes,
            batch: $quantity > 0 ? [
                'batch_no' => $referenceId ?: 'ADJ-' . now()->format('YmdHis'),
                'expiry_date' => null,
                'purchase_price' => (float) $inventory->purchase_price,
                'supplier_id' => $inventory->supplier_id,
            ] : null,
        );
    }

    protected static function apply(
        Inventory $inventory,
        int $delta,
        string $operationType,
        ?string $referenceId = null,
        ?string $notes = null,
        ?array $batch = null,
    ): InventoryTransaction {
        if ($delta === 0) {
            throw new RuntimeException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($inventory, $delta, $operationType, $referenceId, $notes, $batch): InventoryTransaction {
            $item = Inventory::query()
                ->where('item_id', $inventory->item_id)
                ->lockForUpdate()
                ->firstOrFail();

            $nextQuantity = (int) $item->quantity + $delta;

            if ($nextQuantity < 0) {
                throw new RuntimeException("Insufficient stock for {$item->item_name}.");
            }

            if ($delta > 0) {
                $batch ??= [
                    'batch_no' => 'ADJ-' . now()->format('YmdHis'),
                    'expiry_date' => null,
                    'purchase_price' => (float) $item->purchase_price,
                    'supplier_id' => $item->supplier_id,
                ];

                $lot = PharmacyBatch::query()
                    ->where('inventory_item_id', $item->item_id)
                    ->where('batch_no', $batch['batch_no'])
                    ->lockForUpdate()
                    ->first();

                $existingExpiry = $lot?->expiry_date?->toDateString();
                $incomingExpiry = filled($batch['expiry_date'])
                    ? Carbon::parse($batch['expiry_date'])->toDateString()
                    : null;

                if ($lot && (
                    $existingExpiry !== $incomingExpiry
                    || (string) $lot->supplier_id !== (string) $batch['supplier_id']
                )) {
                    throw new RuntimeException('This batch number already exists with different expiry or supplier details.');
                }

                if ($lot) {
                    $lot->increment('quantity_received', $delta);
                    $lot->increment('quantity_remaining', $delta);
                } else {
                    $lot = PharmacyBatch::query()->create([
                        'inventory_item_id' => $item->item_id,
                        'batch_no' => $batch['batch_no'],
                        'expiry_date' => $batch['expiry_date'],
                        'quantity_received' => $delta,
                        'quantity_remaining' => $delta,
                        'purchase_price' => $batch['purchase_price'],
                        'supplier_id' => $batch['supplier_id'],
                        'reference' => $referenceId,
                        'received_at' => now(),
                        'received_by' => Auth::id(),
                    ]);
                }

                PharmacyBatchMovement::query()->create([
                    'pharmacy_batch_id' => $lot->id,
                    'operation_type' => $operationType,
                    'quantity_change' => $delta,
                    'reference' => $referenceId,
                    'notes' => $notes,
                    'user_id' => Auth::id(),
                ]);
            } else {
                static::consumeBatches($item, abs($delta), $operationType, $referenceId, $notes);
            }

            $itemUpdates = ['quantity' => $nextQuantity];
            if ($operationType === 'purchase' && $batch !== null) {
                $itemUpdates['purchase_price'] = $batch['purchase_price'];
                $itemUpdates['supplier_id'] = $batch['supplier_id'];
            }
            $item->update($itemUpdates);

            $earliestExpiry = PharmacyBatch::query()
                ->where('inventory_item_id', $item->item_id)
                ->where('quantity_remaining', '>', 0)
                ->whereNotNull('expiry_date')
                ->min('expiry_date');

            $item->update(['expiry_date' => $earliestExpiry]);

            if ($delta > 0 && static::availableQuantity($item) > 0) {
                Pharmacy::query()
                    ->where('inventory_item_id', $item->item_id)
                    ->where('queue_status', 'backordered')
                    ->update(['queue_status' => 'pending']);
            }

            return InventoryTransaction::query()->create([
                'inventory_id' => $item->item_id,
                'quantity' => $delta,
                'operation_type' => $operationType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'user_id' => Auth::id(),
            ]);
        });
    }

    public static function availableQuantity(Inventory $inventory): int
    {
        return (int) PharmacyBatch::query()
            ->where('inventory_item_id', $inventory->item_id)
            ->where('quantity_remaining', '>', 0)
            ->where(fn ($query) => $query
                ->whereNull('expiry_date')
                ->orWhereDate('expiry_date', '>=', today()))
            ->sum('quantity_remaining');
    }

    protected static function consumeBatches(
        Inventory $item,
        int $quantity,
        string $operationType,
        ?string $referenceId,
        ?string $notes,
    ): void {
        $batches = PharmacyBatch::query()
            ->where('inventory_item_id', $item->item_id)
            ->where('quantity_remaining', '>', 0)
            ->when($operationType === 'expired', fn ($query) => $query
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', today()))
            ->when($operationType !== 'expired', fn ($query) => $query
                ->where(fn ($expiry) => $expiry
                    ->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', today())))
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $available = (int) $batches->sum('quantity_remaining');

        if ($available < $quantity) {
            $reason = $operationType === 'expired' ? 'expired batch stock' : 'unexpired batch stock';
            throw new RuntimeException("Only {$available} units are available in {$reason} for {$item->item_name}.");
        }

        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining === 0) {
                break;
            }

            $taken = min($remaining, (int) $batch->quantity_remaining);
            $batch->decrement('quantity_remaining', $taken);

            PharmacyBatchMovement::query()->create([
                'pharmacy_batch_id' => $batch->id,
                'operation_type' => $operationType,
                'quantity_change' => -$taken,
                'reference' => $referenceId,
                'notes' => $notes,
                'user_id' => Auth::id(),
            ]);

            $remaining -= $taken;
        }
    }
}
