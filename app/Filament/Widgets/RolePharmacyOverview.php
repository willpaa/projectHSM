<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inventories\InventoryResource;
use App\Filament\Resources\Pharmacies\PharmacyResource;
use App\Filament\Resources\PharmacySales\PharmacySaleResource;
use App\Filament\Resources\PharmacyBatches\PharmacyBatchResource;
use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\PharmacySale;
use App\Models\Pharmacy;
use App\Models\PharmacyBatch;
use App\Models\Prescription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RolePharmacyOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $todayStart = today();
        $todayEnd = $todayStart->copy()->endOfDay();

        $todayPrescriptions = Prescription::query()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $todayDispensed = abs((int) InventoryTransaction::query()
            ->where('operation_type', 'dispense')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('quantity'));

        $todaySales = (float) PharmacySale::query()
            ->whereBetween('sold_at', [$todayStart, $todayEnd])
            ->sum('total_amount');

        $lowStock = Inventory::query()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->count();

        $expiryStats = PharmacyBatch::query()
            ->where('quantity_remaining', '>', 0)
            ->whereNotNull('expiry_date')
            ->selectRaw('SUM(CASE WHEN expiry_date >= ? AND expiry_date <= ? THEN 1 ELSE 0 END) as expiring_soon', [today()->toDateString(), today()->addDays(30)->toDateString()])
            ->selectRaw('SUM(CASE WHEN expiry_date < ? THEN 1 ELSE 0 END) as expired_stock', [today()->toDateString()])
            ->first();

        $expiredSoon = (int) $expiryStats->expiring_soon;
        $expiredStock = (int) $expiryStats->expired_stock;

        $pharmacyQueue = Pharmacy::query()
            ->whereIn('queue_status', ['pending', 'backordered', 'partially_dispensed'])
            ->count();

        return [
            Stat::make('Today Prescriptions', (string) $todayPrescriptions)
                ->description('New prescriptions created today')
                ->icon('heroicon-o-document-text')
                 ->color('gray')
                ->url(PrescriptionResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#37d7b4')),
            Stat::make('Today Dispensed', (string) $todayDispensed)
                ->description('Medicines dispensed today')
                ->icon('heroicon-o-check-badge')
                ->color('gray')
                ->url(PharmacyResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#68a4f3')),
            Stat::make('Today Sales', 'ETB' . number_format($todaySales, 2))
                ->description('Pharmacy sales recorded today')
                ->icon('heroicon-o-banknotes')
                 ->color('gray')
                ->url(PharmacySaleResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#8167f1')),
            Stat::make('Low Stock', (string) $lowStock)
                ->description('Items at or below reorder level')
                ->icon('heroicon-o-exclamation-triangle')
                ->url(InventoryResource::getUrl('index'))
                 ->color('gray')
                ->extraAttributes($this->cardStyle('#fef3c7')),
            Stat::make('Pharmacy Queue', (string) $pharmacyQueue)
                ->description('Pending, backordered, or partial orders')
                ->icon('heroicon-o-queue-list')
                ->url(PharmacyResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#cffafe')),
            Stat::make('Expiring Soon', (string) $expiredSoon)
                ->description('Unexpired lots expiring within 30 days')
                ->icon('heroicon-o-clock')
                ->url(PharmacyBatchResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#d1fae5')),
            Stat::make('Expired Stock', (string) $expiredStock)
                ->description('Lots requiring quarantine or removal')
                ->icon('heroicon-o-exclamation-circle')
                ->url(PharmacyBatchResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#fee2e2')),
        ];
    }

    protected function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'xl' => 3,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function cardStyle(string $background, string $textColor = '#0f172a'): array
    {
        return [
            'style' => "background: {$background}; color: {$textColor}; border-radius: 0.8rem;",
            'class' => 'rounded-2xl shadow-lg transition duration-300 ease-out hover:-translate-y-1 hover:scale-[1.01] hover:shadow-2xl',
        ];
    }
}
