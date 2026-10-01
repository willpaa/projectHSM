<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\PharmacySale;
use Illuminate\Support\Facades\DB;

class PharmacyDirectSaleService
{
    public static function sell(Inventory $inventory, int $quantity, string $invoiceId, ?int $patientId, ?string $notes, ?int $userId): PharmacySale
    {
        return DB::transaction(function () use ($inventory, $quantity, $invoiceId, $patientId, $notes, $userId): PharmacySale {
            InventoryService::saleInvoice($inventory, $quantity, $invoiceId, $notes);

            return PharmacySale::query()->create([
                'sale_id' => $invoiceId,
                'sale_type' => 'direct_sale',
                'pharmacy_id' => null,
                'inventory_item_id' => $inventory->item_id,
                'prescription_id' => null,
                'patient_id' => $patientId,
                'medicine_name' => $inventory->item_name,
                'quantity' => $quantity,
                'unit_price' => $inventory->selling_price,
                'total_amount' => $inventory->selling_price * $quantity,
                'payment_status' => 'paid',
                'notes' => $notes ?: 'Direct inventory sale',
                'sold_by' => $userId,
                'sold_at' => now(),
            ]);
        });
    }
}
