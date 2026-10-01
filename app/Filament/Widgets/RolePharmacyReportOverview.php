<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\PharmacyReportDashboard;
use App\Services\PharmacyReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RolePharmacyReportOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Pharmacy Report — This Month';

    protected ?string $description = 'Sales, purchasing and dispensing summary for the current month.';

    protected function getStats(): array
    {
        $report = PharmacyReportService::build(
            now()->startOfMonth()->toDateString(),
            today()->toDateString(),
        );
        $summary = $report['summary'];
        $topMedicine = $report['top_medicines']->first();
        $reportUrl = PharmacyReportDashboard::getUrl();

        return [
            Stat::make('Sales Revenue', 'ETB ' . number_format($summary['sales_value'], 2))
                ->description('Revenue from recorded pharmacy sales')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url($reportUrl),
            Stat::make('Purchases', 'ETB ' . number_format($summary['purchase_value'], 2))
                ->description(number_format($summary['purchase_qty']) . ' units received')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->url($reportUrl),
            Stat::make('Dispensed', number_format($summary['dispense_qty']) . ' units')
                ->description(number_format($summary['prescription_sale_qty']) . ' prescription units sold')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->url($reportUrl),
            Stat::make('Top Medicine', $topMedicine?->medicine_name ?? 'No sales yet')
                ->description($topMedicine
                    ? number_format((int) $topMedicine->total_quantity) . ' units sold this month'
                    : 'No pharmacy sales recorded this month')
                ->icon('heroicon-o-chart-bar')
                ->color('primary')
                ->url($reportUrl),
        ];
    }

    protected function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'xl' => 4,
        ];
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
