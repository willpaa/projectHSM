<?php

namespace App\Filament\Resources\RadiologyTechnicians\Pages;

use App\Filament\Resources\RadiologyTechnicians\RadiologyTechnicianResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRadiologyTechnician extends ViewRecord
{
    protected static string $resource = RadiologyTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
