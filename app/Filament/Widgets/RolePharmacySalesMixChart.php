<?php

namespace App\Filament\Widgets;

use App\Models\PharmacySale;
use Filament\Widgets\ChartWidget;

class RolePharmacySalesMixChart extends ChartWidget
{
    protected ?string $heading = 'Dispensing Mix This Month';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = 1;

    protected ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $quantities = PharmacySale::query()
            ->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfDay()])
            ->selectRaw('sale_type, SUM(quantity) as total_quantity')
            ->groupBy('sale_type')
            ->pluck('total_quantity', 'sale_type');

        return [
            'datasets' => [[
                'label' => 'Units',
                'data' => [
                    (int) ($quantities['prescription'] ?? 0),
                    (int) ($quantities['direct_sale'] ?? 0),
                ],
                'backgroundColor' => ['#0f766e', '#f59e0b'],
                'borderColor' => ['#ffffff', '#ffffff'],
                'borderWidth' => 3,
                'hoverOffset' => 12,
            ]],
            'labels' => ['Prescription dispensing', 'Direct sales'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole([
            'admin',
            'super_admin',
            'pharmacy_staff',
            'pharmacist',
            'pharmacy',
            'pharmcy',
        ]) ?? false;
    }
}
