<?php

namespace App\Filament\Pages;

use App\Models\Payments;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class FinanceReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Finance Report';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.finance-report';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole([
            'admin',
            'super_admin',
            'cashier',
            'casher',
            'billing_staff',
            'accountant',
        ]) ?? false;
    }

    public function mount(): void
    {
        $this->startDate = request()->query('start_date', now()->startOfMonth()->toDateString());
        $this->endDate = request()->query('end_date', now()->toDateString());
    }

    public function getViewData(): array
    {
        $payments = Payments::query()
            ->with(['patient', 'serviceRequest.service', 'collector'])
            ->whereBetween('payment_date', [
                $this->startDate . ' 00:00:00',
                $this->endDate . ' 23:59:59',
            ])
            ->latest('payment_date')
            ->get();

        $cashPayments = $payments->where('payment_mode', '!=', 'insurance');
        $insurancePayments = $payments->where('payment_mode', 'insurance');

        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'payments' => $payments,
            'summary' => [
                'total' => (float) $payments->sum('amount'),
                'collected' => (float) $cashPayments->sum('amount'),
                'insurance' => (float) $insurancePayments->sum('amount'),
                'transactions' => $payments->count(),
            ],
            'byMode' => $this->groupPayments($payments, fn (Payments $payment): string => ucfirst((string) $payment->payment_mode)),
            'byService' => $this->groupPayments($payments, fn (Payments $payment): string => $payment->serviceRequest?->service?->name ?? 'Unknown service'),
        ];
    }

    private function groupPayments(Collection $payments, callable $label): Collection
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
