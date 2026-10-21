<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Laboratories\LaboratoryResource;
use App\Filament\Resources\Radiologies\RadiologyResource;
use App\Models\Laboratory;
use App\Models\Radiology;
use App\Support\ClinicalStaffAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class RoleClinicalTechnicianOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        if (ClinicalStaffAccess::isRadiologyStaff()) {
            $staffId = ClinicalStaffAccess::currentRadiologistId();
            $query = Radiology::query()->where('radiologist_id', $staffId ?? 0);

            return $this->stats(
                clone $query,
                (clone $query)->whereRaw("LOWER(COALESCE(status, '')) NOT IN (?, ?)", ['completed', 'cancelled']),
                (clone $query)->where('status', 'completed')->whereDate('completed_at', today()),
                'Radiology Requests Assigned to You',
                RadiologyResource::getUrl('index'),
            );
        }

        $staffId = ClinicalStaffAccess::currentLaboratoristId();
        $query = Laboratory::query()->where('laboratorist_id', $staffId ?? 0);

        return $this->stats(
            clone $query,
            (clone $query)->whereRaw("LOWER(COALESCE(status, '')) NOT IN (?, ?)", ['completed', 'cancelled']),
            (clone $query)->where('status', 'completed')->whereDate('result_date', today()),
            'Laboratory Requests Assigned to You',
            LaboratoryResource::getUrl('index'),
        );
    }

    /** @return array<Stat> */
    private function stats(Builder $all, Builder $open, Builder $completedToday, string $label, string $url): array
    {
        return [
            Stat::make($label, (string) $all->count())
                ->description('Linked to your staff account')
                ->icon('heroicon-o-clipboard-document-list')
                ->url($url),
            Stat::make('Open Requests', (string) $open->count())
                ->description('Assigned requests awaiting completion')
                ->icon('heroicon-o-clock')
                ->url($url),
            Stat::make('Completed Today', (string) $completedToday->count())
                ->description('Requests completed under your account')
                ->icon('heroicon-o-check-circle')
                ->url($url),
        ];
    }

    public static function canView(): bool
    {
        return ClinicalStaffAccess::isClinicalStaff();
    }
}
