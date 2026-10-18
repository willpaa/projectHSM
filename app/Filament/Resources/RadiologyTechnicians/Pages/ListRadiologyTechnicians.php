<?php

namespace App\Filament\Resources\RadiologyTechnicians\Pages;

use App\Filament\Resources\RadiologyTechnicians\RadiologyTechnicianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRadiologyTechnicians extends ListRecords
{
    protected static string $resource = RadiologyTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
