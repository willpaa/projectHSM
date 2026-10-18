<?php

namespace App\Services;

use App\Models\Payments;
use Illuminate\Support\Collection;

class FinanceReportService
{
    public static function payments(string $startDate, string $endDate): Collection
    {
        return Payments::query()
            ->with(['patient', 'serviceRequest.service', 'collector'])
            ->whereBetween('payment_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->latest('payment_date')
            ->get();
    }

    /** @return list<list<string|int|float>> */
    public static function rows(Collection $payments): array
    {
        $rows = [['Invoice', 'Patient', 'Service', 'Payment mode', 'Cashier', 'Amount (ETB)', 'Payment date']];

        foreach ($payments as $payment) {
            $rows[] = [
                (string) $payment->invoice_number,
                (string) ($payment->patient?->full_name ?? 'Unknown'),
                (string) ($payment->serviceRequest?->service?->name ?? 'Unknown'),
                ucfirst((string) $payment->payment_mode),
                (string) ($payment->collector?->name ?? 'Cashier'),
                (float) $payment->amount,
                $payment->payment_date?->format('Y-m-d H:i') ?? '',
            ];
        }

        return $rows;
    }

    /** @return array{total: float, collected: float, insurance: float, transactions: int} */
    public static function summary(Collection $payments): array
    {
        $cashPayments = $payments->where('payment_mode', '!=', 'insurance');
        $insurancePayments = $payments->where('payment_mode', 'insurance');

        return [
            'total' => (float) $payments->sum('amount'),
            'collected' => (float) $cashPayments->sum('amount'),
            'insurance' => (float) $insurancePayments->sum('amount'),
            'transactions' => $payments->count(),
        ];
    }

    /** @return Collection<string, array{count: int, amount: float}> */
    public static function byPaymentMode(Collection $payments): Collection
    {
        return static::groupPayments(
            $payments,
            fn (Payments $payment): string => ucfirst((string) $payment->payment_mode),
        );
    }

    /** @return Collection<string, array{count: int, amount: float}> */
    public static function byService(Collection $payments): Collection
    {
        return static::groupPayments(
            $payments,
            fn (Payments $payment): string => $payment->serviceRequest?->service?->name ?? 'Unknown service',
        );
    }

    private static function groupPayments(Collection $payments, callable $label): Collection
    {
        return $payments
            ->groupBy($label)
            ->map(fn (Collection $group): array => [
                'count' => $group->count(),
                'amount' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('amount');
    }
}
