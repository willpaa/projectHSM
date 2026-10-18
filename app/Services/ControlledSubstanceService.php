<?php

namespace App\Services;

use App\Models\ControlledSubstanceRegister;
use App\Models\Inventory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Controlled substance register (Basel / GPP chain-of-custody).
 *
 * Every receipt, dispense, return, transfer or waste of a controlled drug is
 * recorded with a running balance so the physical stock can be reconciled
 * against the ledger at any time (WHO/FIP GPP controlled-drug requirement).
 */
class ControlledSubstanceService
{
    /**
     * @param  array{
     *     batch_no?: ?string,
     *     patient_id?: ?int,
     *     prescription_id?: ?string,
     *     witness?: ?string,
     *     notes?: ?string,
     *     occurred_at?: mixed,
     * } $context
     */
    public static function record(Inventory $inventory, string $transactionType, int $quantity, ?int $pharmacistId = null, array $context = []): ControlledSubstanceRegister
    {
        if (! $inventory->isControlled()) {
            throw new RuntimeException("{$inventory->item_name} is not flagged as a controlled substance.");
        }

        // Signed change to the running balance based on the movement direction.
        $inbound = in_array($transactionType, ['receipt', 'return', 'transfer_in'], true);
        $outbound = in_array($transactionType, ['dispense', 'transfer_out', 'waste', 'loss'], true);

        if (! $inbound && ! $outbound) {
            throw new RuntimeException('Unknown controlled-substance transaction type: ' . $transactionType);
        }

        $quantity = abs($quantity);
        $signed = $inbound ? $quantity : -$quantity;

        return DB::transaction(function () use ($inventory, $transactionType, $quantity, $signed, $pharmacistId, $context): ControlledSubstanceRegister {
            // Lock the latest register row for this item so concurrent movements
            // compute the running balance from a consistent snapshot.
            $lastBalance = (int) ControlledSubstanceRegister::query()
                ->where('inventory_item_id', $inventory->item_id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->value('running_balance');

            return ControlledSubstanceRegister::query()->create([
                'inventory_item_id' => $inventory->item_id,
                'batch_no' => $context['batch_no'] ?? null,
                'transaction_type' => $transactionType,
                'quantity' => $quantity,
                'running_balance' => $lastBalance + $signed,
                'patient_id' => $context['patient_id'] ?? null,
                'prescription_id' => $context['prescription_id'] ?? null,
                'pharmacist_id' => $pharmacistId ?? Auth::id(),
                'witness' => $context['witness'] ?? null,
                'notes' => $context['notes'] ?? null,
                'occurred_at' => $context['occurred_at'] ?? now(),
            ]);
        });
    }

    /**
     * The current ledger balance for a controlled substance.
     */
    public static function currentBalance(Inventory $inventory): int
    {
        return (int) ControlledSubstanceRegister::query()
            ->where('inventory_item_id', $inventory->item_id)
            ->orderByDesc('id')
            ->value('running_balance');
    }
}
