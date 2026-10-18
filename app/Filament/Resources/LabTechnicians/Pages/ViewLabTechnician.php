<?php

namespace App\Filament\Resources\LabTechnicians\Pages;

use App\Filament\Resources\LabTechnicians\LabTechnicianResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLabTechnician extends ViewRecord
{
    protected static string $resource = LabTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
