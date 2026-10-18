<?php

namespace App\Filament\Resources\LabTechnicians\Pages;

use App\Filament\Resources\LabTechnicians\LabTechnicianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLabTechnicians extends ListRecords
{
    protected static string $resource = LabTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
