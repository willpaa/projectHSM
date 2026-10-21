<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IPDS\IPDResource;
use App\Models\Beds;
use App\Support\ClinicalNurseAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoleNurseOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $nurseId = ClinicalNurseAccess::currentNurseId('nurse');
        $assignedBeds = Beds::query()->where('assigned_nurse_id', $nurseId ?? 0);

        return [
            Stat::make('Beds Assigned to You', (string) (clone $assignedBeds)->count())
                ->description('Beds linked to your nurse account')
                ->icon('heroicon-o-rectangle-stack')
                ->url(IPDResource::getUrl('index')),
            Stat::make('Patients in Your Care', (string) (clone $assignedBeds)
                ->where('occupancy_status', 'occupied')
                ->whereNotNull('current_patient_id')
                ->count())
                ->description('Currently admitted in your assigned beds')
                ->icon('heroicon-o-user-group')
                ->url(IPDResource::getUrl('index')),
            Stat::make('Available Beds in Your Assignment', (string) (clone $assignedBeds)
                ->where('occupancy_status', 'available')
                ->count())
                ->description('Assigned beds with no current patient')
                ->icon('heroicon-o-check-circle')
                ->url(IPDResource::getUrl('index')),
        ];
    }

    public static function canView(): bool
    {
        return ClinicalNurseAccess::isNurse();
    }
}
