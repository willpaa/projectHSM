<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderService
{
    /**
     * Receive goods against a purchase order.
     *
     * $receipts is keyed by purchase_order_item id, each value being:
     * [
     *   'quantity'   => int,     // how many units received for this line
     *   'batch_no'   => ?string, // optional batch/lot number
     *   'expiry_date'=> ?string, // optional expiry date (Y-m-d)
     *   'unit_cost'  => ?float,  // optional updated unit cost
     * ]
     *
     * Each received line reuses InventoryService::purchaseOrder() so batch
     * tracking (FEFO), the stock ledger and backorder flipping all behave
     * exactly like a normal stock purchase.
     */
    public static function receive(PurchaseOrder $order, array $receipts, ?int $userId = null): PurchaseOrder
    {
        if (! $order->isReceivable()) {
            throw new RuntimeException('This purchase order can no longer receive goods.');
        }

        $userId = $userId ?? Auth::id();

        return DB::transaction(function () use ($order, $receipts, $userId): PurchaseOrder {
            $order = PurchaseOrder::query()->lockForUpdate()->with('items')->findOrFail($order->getKey());

            $receivedAny = false;

            foreach ($order->items as $item) {
                $line = $receipts[$item->getKey()] ?? null;

                if (! $line) {
                    continue;
                }

                $quantity = (int) ($line['quantity'] ?? 0);

                if ($quantity < 1) {
                    continue;
                }

                if ($quantity > $item->outstanding()) {
                    throw new RuntimeException("Cannot receive more than the outstanding quantity for {$item->inventory_item_id}.");
                }

                $inventory = Inventory::query()->where('item_id', $item->inventory_item_id)->first();

                if (! $inventory) {
                    throw new RuntimeException("Inventory item {$item->inventory_item_id} no longer exists.");
                }

                $unitCost = isset($line['unit_cost']) ? (float) $line['unit_cost'] : (float) $item->unit_cost;
                $batchNo = $line['batch_no'] ?? null;
                $expiryDate = $line['expiry_date'] ?? null;

                // Route the stock increase through the existing inventory service so
                // batches, the transaction ledger and backorder status stay consistent.
                InventoryService::purchaseOrder(
                    inventory: $inventory,
                    quantity: $quantity,
                    referenceId: $order->po_number,
                    notes: "Goods received against PO {$order->po_number}",
                    batchNo: $batchNo ?: 'PO-' . $order->po_number . '-' . $item->getKey(),
                    expiryDate: $expiryDate,
                    purchasePrice: $unitCost,
                    supplierId: (string) $order->supplier_id,
                );

                // Keep the inventory supplier reference in sync (it is a plain string column).
                $inventory->update(['supplier_id' => (string) $order->supplier_id]);

                $item->increment('quantity_received', $quantity);
                $item->update([
                    'unit_cost' => $unitCost,
                    'line_total' => $unitCost * (int) $item->quantity_ordered,
                ]);

                $receivedAny = true;
            }

            if (! $receivedAny) {
                throw new RuntimeException('Enter a quantity of at least one for at least one item.');
            }

            $order->refresh()->load('items');

            $order->update([
                'status' => $order->isFullyReceived() ? 'received' : 'partial',
                'total_amount' => (float) $order->items->sum('line_total'),
                'received_by' => $userId,
                'received_at' => $order->isFullyReceived() ? now() : $order->received_at,
            ]);

            return $order->refresh();
        });
    }
}
