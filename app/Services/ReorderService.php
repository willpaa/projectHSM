<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Automated reorder-point purchasing (WHO/FIP GPP procurement).
 *
 * Scans inventory for items at or below their reorder level and generates a
 * draft purchase order per supplier, so low stock is replenished through the
 * standard procurement workflow instead of ad-hoc notes.
 */
class ReorderService
{
    /**
     * Items whose on-hand quantity has reached the reorder level.
     *
     * @return \Illuminate\Support\Collection<int, Inventory>
     */
    public static function lowStockItems(): \Illuminate\Support\Collection
    {
        return Inventory::query()
            ->whereNotNull('supplier_id')
            ->where('reorder_level', '>', 0)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->orderBy('item_name')
            ->get();
    }

    /**
     * Generate one draft purchase order per supplier for all low-stock items.
     *
     * The suggested order quantity brings each item up to twice its reorder
     * level (a simple min-max replenishment policy).
     *
     * @return \Illuminate\Support\Collection<int, PurchaseOrder>
     */
    public static function generateDraftPurchaseOrders(?int $userId = null): \Illuminate\Support\Collection
    {
        $userId = $userId ?? Auth::id();

        $items = static::lowStockItems();

        if ($items->isEmpty()) {
            throw new RuntimeException('No items are currently at or below their reorder level.');
        }

        return DB::transaction(function () use ($items, $userId): \Illuminate\Support\Collection {
            $orders = collect();

            foreach ($items->groupBy('supplier_id') as $supplierId => $supplierItems) {
                /** @var Supplier|null $supplier */
                $supplier = Supplier::query()->whereKey($supplierId)->first();

                if (! $supplier) {
                    continue;
                }

                
                $poNumber = 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));

                $order = PurchaseOrder::query()->create([
                    'po_number' => $poNumber,
                    'supplier_id' => $supplier->id,
                    'order_date' => now()->toDateString(),
                    'status' => 'draft',
                    'total_amount' => 0,
                    'notes' => 'Auto-generated from reorder levels.',
                    'created_by' => $userId,
                ]);

                $lineTotal = 0.0;

                foreach ($supplierItems as $item) {
                    $reorderLevel = (int) $item->reorder_level;
                    $suggestedQty = max($reorderLevel, ($reorderLevel * 2) - (int) $item->quantity);
                    $unitCost = (float) $item->purchase_price;

                    PurchaseOrderItem::query()->create([
                        'purchase_order_id' => $order->id,
                        'inventory_item_id' => $item->item_id,
                        'quantity_ordered' => $suggestedQty,
                        'quantity_received' => 0,
                        'unit_cost' => $unitCost,
                        'line_total' => $unitCost * $suggestedQty,
                    ]);

                    $lineTotal += $unitCost * $suggestedQty;
                }

                $order->update(['total_amount' => $lineTotal]);

                $orders->push($order->refresh());
            }

            return $orders;
        });
    }
}
