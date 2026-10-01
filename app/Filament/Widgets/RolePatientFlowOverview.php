<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IPDS\IPDResource;
use App\Filament\Resources\Laboratories\LaboratoryResource;
use App\Filament\Resources\OPDS\OPDResource;
use App\Filament\Resources\Patients\PatientsResource;
use App\Filament\Resources\Radiologies\RadiologyResource;
use App\Models\IPD;
use App\Models\Laboratory;
use App\Models\OPD;
use App\Models\Patients;
use App\Models\Radiology;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RolePatientFlowOverview extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $newPatientsToday = Patients::query()
            ->whereDate('created_at', today())
            ->count();
        $opdCount = OPD::query()->count();
        $ipdCount = IPD::query()->count();
        $labCount = Laboratory::query()->count();
        $radiologyCount = Radiology::query()->count();

        return [
            Stat::make('New Patients Today', (string) $newPatientsToday)
                ->description('Registered today')
                ->icon('heroicon-o-user-plus')
                ->url(PatientsResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#0EA5E9')),
            Stat::make('OPD Encounters', (string) $opdCount)
                ->description('Outpatient records')
                ->icon('heroicon-o-clipboard-document-list')
                ->url(OPDResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#1D4ED8')),
            Stat::make('IPD Encounters', (string) $ipdCount)
                ->description('Inpatient records')
                ->icon('heroicon-o-building-office-2')
                ->url(IPDResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#7C3AED')),
            Stat::make('Lab Requests', (string) $labCount)
                ->description('Laboratory workload')
                ->icon('heroicon-o-beaker')
                ->url(LaboratoryResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#0F766E')),
            Stat::make('Radiology Orders', (string) $radiologyCount)
                ->description('Imaging workload')
                ->icon('heroicon-o-photo')
                ->url(RadiologyResource::getUrl('index'))
                ->extraAttributes($this->cardStyle('#B45309')),
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
