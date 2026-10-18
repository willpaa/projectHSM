<?php

namespace App\Services;

use App\Models\PharmacySale;
use Illuminate\Support\Collection;

/**
 * Records which physical batches were consumed by a sale (FEFO allocation)
 * and snapshots COGS/profit on the sale row so finance reports don't need
 * to recompute allocations.
 */
class SaleAllocationService
{
    /**
     * @param  Collection<int, array{batch: \App\Models\PharmacyBatch, quantity: int, unit_cost: float}>  $allocations
     */
    public static function record(PharmacySale $sale, Collection $allocations): PharmacySale
    {
        $cogs = 0.0;

        foreach ($allocations as $allocation) {
            $cost = round($allocation['unit_cost'] * $allocation['quantity'], 2);
            $cogs += $cost;

            $sale->batches()->create([
                'pharmacy_batch_id' => $allocation['batch']->id,
                'quantity' => $allocation['quantity'],
                'unit_cost' => $allocation['unit_cost'],
                'cost_amount' => $cost,
            ]);
        }

        $sale->forceFill([
            'cogs_amount' => round($cogs, 2),
            'profit_amount' => round((float) $sale->total_amount - $cogs, 2),
        ])->save();

        return $sale->refresh();
    }
}
