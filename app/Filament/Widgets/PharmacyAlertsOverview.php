<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\PharmacyBatch;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PharmacyAlertsOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Pharmacy Alerts';

    protected ?string $description = 'Low-stock and near-expiry items that need attention today.';

    protected function getStats(): array
    {
        $lowStock = Inventory::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->count();

        $expiringSoon = PharmacyBatch::query()
            ->where('quantity_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [today(), today()->addDays(30)])
            ->count();

        $expiredBatches = PharmacyBatch::query()
            ->where('quantity_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', today())
            ->count();

        return [
            Stat::make('Low Stock Items', number_format($lowStock))
                ->description('At or below reorder level — generate reorder POs')
                ->icon('heroicon-o-arrow-down-tray')
                ->color($lowStock > 0 ? 'warning' : 'success'),
            Stat::make('Expiring Within 30 Days', number_format($expiringSoon))
                ->description('Batches to prioritise (FEFO) or discount')
                ->icon('heroicon-o-clock')
                ->color($expiringSoon > 0 ? 'warning' : 'success'),
            Stat::make('Expired Stock', number_format($expiredBatches))
                ->description('Batches pending write-off removal')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($expiredBatches > 0 ? 'danger' : 'success'),
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
