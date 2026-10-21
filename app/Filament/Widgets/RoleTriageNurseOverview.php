<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Triages\TriageResource;
use App\Models\Triage;
use App\Support\ClinicalNurseAccess;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RoleTriageNurseOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $nurseId = ClinicalNurseAccess::currentNurseId('triage_nurse');
        $assigned = Triage::query()->where('nurse_id', $nurseId ?? 0);
        $unassigned = Triage::query()
            ->whereNull('nurse_id')
            ->whereIn('status', ['waiting', 'in_progress']);

        return [
            Stat::make('My Open Triage Cases', (string) (clone $assigned)
                ->whereIn('status', ['waiting', 'in_progress'])
                ->count())
                ->description('Cases assigned to your account')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(TriageResource::getUrl('index')),
            Stat::make('Waiting to Be Claimed', (string) $unassigned->count())
                ->description('Unassigned patients in the triage queue')
                ->icon('heroicon-o-clock')
                ->url(TriageResource::getUrl('index')),
            Stat::make('Sent to OPD Today', (string) (clone $assigned)
                ->where('status', 'sent_to_opd')
                ->whereDate('updated_at', today())
                ->count())
                ->description('Patients you assessed and referred today')
                ->icon('heroicon-o-arrow-right-circle')
                ->url(TriageResource::getUrl('index')),
        ];
    }

    public static function canView(): bool
    {
        return ClinicalNurseAccess::isTriageNurse();
    }
}
