<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\PharmacyBatch;
use App\Models\PharmacySale;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PharmacyReportService
{
    public static function build(?string $startDate = null, ?string $endDate = null): array
    {
        [$start, $end] = static::resolveRange($startDate, $endDate);
        $transactionsTable = (new InventoryTransaction())->getTable();
        $inventoryTable = (new Inventory())->getTable();

        $transactionsQuery = InventoryTransaction::query()
            ->with('inventory')
            ->whereBetween($transactionsTable . '.created_at', [$start, $end]);

        $salesQuery = PharmacySale::query()
            ->with(['patient', 'seller'])
            ->whereBetween('sold_at', [$start, $end]);

        $transactionSummary = (clone $transactionsQuery)
            ->setEagerLoads([])
            ->leftJoin($inventoryTable . ' as report_inventory', 'report_inventory.item_id', '=', $transactionsTable . '.inventory_id')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN ' . $transactionsTable . '.operation_type = ? THEN ' . $transactionsTable . '.quantity ELSE 0 END), 0) as purchase_qty',
                ['purchase'],
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN ' . $transactionsTable . '.operation_type = ? THEN ABS(' . $transactionsTable . '.quantity * report_inventory.purchase_price) ELSE 0 END), 0) as purchase_value',
                ['purchase'],
            )
            ->selectRaw(
                'ABS(COALESCE(SUM(CASE WHEN ' . $transactionsTable . '.operation_type = ? THEN ' . $transactionsTable . '.quantity ELSE 0 END), 0)) as dispense_qty',
                ['dispense'],
            )
            ->first();

        $salesSummary = (clone $salesQuery)
            ->setEagerLoads([])
            ->selectRaw('COALESCE(SUM(CASE WHEN sale_type = ? THEN quantity ELSE 0 END), 0) as direct_sale_qty', ['direct_sale'])
            ->selectRaw('COALESCE(SUM(CASE WHEN sale_type = ? THEN quantity ELSE 0 END), 0) as prescription_sale_qty', ['prescription'])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as sales_value')
            ->first();

        $profitSummary = (clone $salesQuery)
            ->setEagerLoads([])
            ->selectRaw('COALESCE(SUM(cogs_amount), 0) as cogs_value')
            ->selectRaw('COALESCE(SUM(profit_amount), 0) as profit_value')
            ->selectRaw('COALESCE(SUM(CASE WHEN cogs_amount = 0 THEN total_amount ELSE 0 END), 0) as unallocated_value')
            ->first();

        $stockValuation = static::stockValuation();

        $recentTransactions = (clone $transactionsQuery)
            ->latest()
            ->limit(12)
            ->get();

        $recentSales = (clone $salesQuery)
            ->latest('sold_at')
            ->limit(12)
            ->get();

        $topMedicines = (clone $salesQuery)
            ->selectRaw('medicine_name, SUM(quantity) as total_quantity, SUM(total_amount) as total_amount')
            ->groupBy('medicine_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        return [
            'start' => $start,
            'end' => $end,
            'summary' => [
                'purchase_qty' => (int) $transactionSummary->purchase_qty,
                'purchase_value' => (float) $transactionSummary->purchase_value,
                'direct_sale_qty' => (int) $salesSummary->direct_sale_qty,
                'prescription_sale_qty' => (int) $salesSummary->prescription_sale_qty,
                'dispense_qty' => (int) $transactionSummary->dispense_qty,
                'sales_value' => (float) $salesSummary->sales_value,
                'cogs_value' => (float) $profitSummary->cogs_value,
                'profit_value' => (float) $profitSummary->profit_value,
                'unallocated_value' => (float) $profitSummary->unallocated_value,
                'stock_quantity' => $stockValuation['quantity'],
                'stock_value' => $stockValuation['value'],
                'expired_stock_value' => $stockValuation['expired_value'],
            ],
            'stock_valuation_items' => $stockValuation['items'],
            'recent_transactions' => $recentTransactions,
            'recent_sales' => $recentSales,
            'top_medicines' => $topMedicines,
        ];
    }

    /**
     * Current inventory value, valued at each batch's actual purchase price
     * (FIFO/batch valuation). Also reports expired stock still sitting on shelves.
     *
     * @return array{quantity: int, value: float, expired_value: float, items: Collection}
     */
    public static function stockValuation(): array
    {
        $batches = PharmacyBatch::query()
            ->with('inventoryItem')
            ->where('quantity_remaining', '>', 0)
            ->get();

        $value = 0.0;
        $expiredValue = 0.0;
        $quantity = 0;
        $items = collect();

        foreach ($batches as $batch) {
            $lineValue = (int) $batch->quantity_remaining * (float) $batch->purchase_price;
            $value += $lineValue;
            $quantity += (int) $batch->quantity_remaining;

            $isExpired = $batch->expiry_date !== null && $batch->expiry_date->lt(today());
            if ($isExpired) {
                $expiredValue += $lineValue;
            }

            $items->push([
                'item_id' => $batch->inventory_item_id,
                'item_name' => $batch->inventoryItem?->item_name,
                'batch_no' => $batch->batch_no,
                'expiry_date' => $batch->expiry_date?->toDateString(),
                'quantity_remaining' => (int) $batch->quantity_remaining,
                'unit_cost' => (float) $batch->purchase_price,
                'line_value' => round($lineValue, 2),
                'expired' => $isExpired,
            ]);
        }

        return [
            'quantity' => $quantity,
            'value' => round($value, 2),
            'expired_value' => round($expiredValue, 2),
            'items' => $items->sortByDesc('line_value')->values(),
        ];
    }

    public static function rowsForCsv(?string $startDate = null, ?string $endDate = null): Collection
    {
        $report = static::build($startDate, $endDate);

        return collect($report['recent_sales'])->map(function (PharmacySale $sale): array {
            return [
                'sale_id' => $sale->sale_id,
                'sale_type' => $sale->sale_type,
                'patient' => $sale->patient?->full_name,
                'prescription_id' => $sale->prescription_id,
                'medicine_name' => $sale->medicine_name,
                'quantity' => $sale->quantity,
                'unit_price' => $sale->unit_price,
                'total_amount' => $sale->total_amount,
                'payment_status' => $sale->payment_status,
                'sold_by' => $sale->seller?->name,
                'sold_at' => optional($sale->sold_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    protected static function resolveRange(?string $startDate, ?string $endDate): array
    {
        $start = filled($startDate)
            ? Carbon::parse($startDate)->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $end = filled($endDate)
            ? Carbon::parse($endDate)->endOfDay()
            : now()->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }
}
