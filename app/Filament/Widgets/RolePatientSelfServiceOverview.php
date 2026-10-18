<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\PatientPortal;
use App\Filament\Resources\Appointments\AppointmentsResource;
use App\Models\Appointments;
use App\Models\Patients;
use App\Models\Payments;
use App\Models\Prescription;
use App\Models\ServiceRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RolePatientSelfServiceOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = auth()->user();
        $patientId = $user
            ? Patients::query()->where('user_id', $user->id)->value('id')
            : null;

        $appointmentsCount = $patientId
            ? Appointments::query()->where('patient_id', $patientId)->count()
            : 0;
        $upcomingCount = $patientId
            ? Appointments::query()
                ->where('patient_id', $patientId)
                ->where('date', '>=', today()->toDateString())
                ->whereIn('status', ['requested', 'scheduled', 'checked_in', 'in_progress'])
                ->count()
            : 0;
        $prescriptionsCount = $patientId
            ? Prescription::query()->where('patient_id', $patientId)->count()
            : 0;
        $outstandingAmount = $patientId
            ? ServiceRequest::query()
                ->where('patient_id', $patientId)
                ->selectRaw('COALESCE(SUM(GREATEST(patient_share - discount - paid_amount, 0)), 0) as outstanding_amount')
                ->value('outstanding_amount')
            : 0.0;
        $paymentsCount = $patientId
            ? Payments::query()->where('patient_id', $patientId)->count()
            : 0;

        return [
            Stat::make('My Appointments', (string) $appointmentsCount)
                ->description('All my bookings')
                ->icon('heroicon-o-calendar-days')
                ->url(AppointmentsResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#1D4ED8')),
            Stat::make('Upcoming', (string) $upcomingCount)
                ->description('Scheduled from today')
                ->icon('heroicon-o-clock')
                ->url(PatientPortal::getUrl())
                ->extraAttributes($this->cardStyle('#0EA5E9')),
            Stat::make('My Prescriptions', (string) $prescriptionsCount)
                ->description('Issued for me')
                ->icon('heroicon-o-document-text')
                ->url(PatientPortal::getUrl())
                ->extraAttributes($this->cardStyle('#7C3AED')),
            Stat::make('Outstanding Bill', 'ETB ' . number_format($outstandingAmount, 2))
                ->description('My balance due')
                ->icon('heroicon-o-exclamation-triangle')
                ->url(PatientPortal::getUrl())
                ->extraAttributes($this->cardStyle('#B91C1C')),
            Stat::make('My Payments', (string) $paymentsCount)
                ->description('Billing records')
                ->icon('heroicon-o-credit-card')
                ->url(PatientPortal::getUrl())
                ->extraAttributes($this->cardStyle('#059669')),
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
