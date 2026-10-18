<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ServiceRequests\ServiceRequestResource;
use App\Models\Payments;
use App\Models\ServiceRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CashierOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '10s';

    protected function getStats(): array
    {
        $todayStart = today();
        $todayEnd = $todayStart->copy()->endOfDay();
        $serviceRequestStats = ServiceRequest::query()
            ->selectRaw('SUM(CASE WHEN payment_status IN (?, ?) THEN 1 ELSE 0 END) as pending_count', ['pending', 'verified'])
            ->selectRaw('SUM(CASE WHEN payment_status = ? AND requested_at < ? THEN 1 ELSE 0 END) as urgent_count', ['pending', now()->subMinutes(30)])
            ->selectRaw('SUM(CASE WHEN payment_status = ? AND paid_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as paid_today_count', ['paid', $todayStart, $todayEnd])
            ->first();

        $paymentStats = Payments::query()
            ->whereBetween('payment_date', [$todayStart, $todayEnd])
            ->selectRaw('SUM(CASE WHEN payment_mode IN (?, ?, ?, ?, ?) THEN amount ELSE 0 END) as collected_total', ['cash', 'card', 'upi', 'netbanking', 'mixed'])
            ->selectRaw('SUM(CASE WHEN payment_mode = ? THEN amount ELSE 0 END) as insurance_total', ['insurance'])
            ->first();

        $pending = (int) $serviceRequestStats->pending_count;
        $urgent = (int) $serviceRequestStats->urgent_count;
        $paidToday = (int) $serviceRequestStats->paid_today_count;
        $collectedToday = (float) $paymentStats->collected_total;
        $insuranceToday = (float) $paymentStats->insurance_total;

        return [
            Stat::make('Pending Payments', (string) $pending)
                ->description('Waiting at cashier')
                ->icon('heroicon-o-clock')
                ->url(ServiceRequestResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#D97706')),
            Stat::make('Urgent Queue', (string) $urgent)
                ->description('Pending over 30 minutes')
                ->icon('heroicon-o-exclamation-triangle')
                ->url(ServiceRequestResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#B91C1C')),
            Stat::make('Paid Today', (string) $paidToday)
                ->description('Completed service payments')
                ->icon('heroicon-o-check-circle')
                ->url(ServiceRequestResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#059669')),
            Stat::make('Collected Today', 'ETB ' . number_format($collectedToday, 2))
                ->description('Cash/card/mobile total')
                ->icon('heroicon-o-banknotes')
                ->url(ServiceRequestResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#0F766E')),
            Stat::make('Insurance Today', 'ETB ' . number_format($insuranceToday, 2))
                ->description('Approved insurance amount')
                ->icon('heroicon-o-shield-check')
                ->url(ServiceRequestResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#1D4ED8')),
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
        $hex = ltrim($background, '#');

        if (strlen($hex) === 6 && ctype_xdigit($hex)) {
            [$red, $green, $blue] = [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ];
            $background = sprintf(
                '#%02x%02x%02x',
                (int) round(($red * 0.16) + (255 * 0.84)),
                (int) round(($green * 0.16) + (255 * 0.84)),
                (int) round(($blue * 0.16) + (255 * 0.84)),
            );
        }

        return [
            'style' => "background: {$background}; color: #0f172a; border-radius: 0.8rem;",
            'class' => 'shadow-sm',
        ];
    }
}
