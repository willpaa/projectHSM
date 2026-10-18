<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Appointments\AppointmentsResource;
use App\Filament\Resources\Patients\PatientsResource;
use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Models\Appointments;
use App\Models\Doctors;
use App\Models\Prescription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class RoleDoctorWorkloadOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $doctorIds = $this->resolveCurrentDoctorIds();
        $userIsDoctor = auth()->user()?->hasRole('doctor') ?? false;
        $hasDoctor = $doctorIds !== [];

        $appointmentsQuery = Appointments::query();
        $prescriptionQuery = Prescription::query();

        if ($hasDoctor) {
            $appointmentsQuery->whereIn('doctor_id', $doctorIds);
            $prescriptionQuery->whereIn('doctor_id', $doctorIds);
        } elseif ($userIsDoctor) {
            $appointmentsQuery->whereRaw('1 = 0');
            $prescriptionQuery->whereRaw('1 = 0');
        }

        $todayAppointments = (clone $appointmentsQuery)
            ->where('date', today()->toDateString())
            ->count();
        $scheduledAppointments = (clone $appointmentsQuery)
            ->whereIn('status', ['requested', 'scheduled', 'checked_in', 'in_progress'])
            ->count();
        $completedAppointments = (clone $appointmentsQuery)
            ->where('status', 'completed')
            ->count();
        $patientCount = (clone $appointmentsQuery)
            ->distinct('patient_id')
            ->count('patient_id');
        $prescriptions = (clone $prescriptionQuery)->count();

        return [
            Stat::make('Appointments Today', (string) $todayAppointments)
                ->description($hasDoctor ? 'Your schedule today' : ($userIsDoctor ? 'Link your doctor profile to see your schedule' : 'All doctors today'))
                ->icon('heroicon-o-calendar-days')
                ->url(AppointmentsResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#0EA5E9')),
            Stat::make('Active appointments', (string) $scheduledAppointments)
                ->description($hasDoctor ? 'Your pending visits' : ($userIsDoctor ? 'Link your doctor profile to see appointments' : 'All pending visits'))
                ->icon('heroicon-o-clock')
                ->url(AppointmentsResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#2563EB')),
            Stat::make('Completed', (string) $completedAppointments)
                ->description($hasDoctor ? 'Your completed visits' : ($userIsDoctor ? 'Link your doctor profile to see completed visits' : 'All completed visits'))
                ->icon('heroicon-o-check-badge')
                ->url(AppointmentsResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#16A34A')),
            Stat::make('Patients', (string) $patientCount)
                ->description($hasDoctor ? 'Unique patients assigned' : ($userIsDoctor ? 'Link your doctor profile to see patients' : 'Unique patients in appointments'))
                ->icon('heroicon-o-users')
                ->url(PatientsResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#7C3AED')),
            Stat::make('Prescriptions', (string) $prescriptions)
                ->description($hasDoctor ? 'Prescriptions you issued' : ($userIsDoctor ? 'Link your doctor profile to see prescriptions' : 'All prescriptions'))
                ->icon('heroicon-o-document-text')
                ->url(PrescriptionResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#0F766E')),
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
     * @return array<int>
     */
    protected function resolveCurrentDoctorIds(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        $query = Doctors::query();

        if (Schema::hasColumn('doctors', 'user_id')) {
            $ids = (clone $query)->where('user_id', $user->id)->pluck('id')->all();
            if ($ids !== []) {
                return $ids;
            }
        }

        return (clone $query)
            ->where('email', $user->email)
            ->pluck('id')
            ->all();
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
