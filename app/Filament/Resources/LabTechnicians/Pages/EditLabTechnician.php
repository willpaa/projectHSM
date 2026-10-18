<?php

namespace App\Filament\Resources\LabTechnicians\Pages;

use App\Filament\Resources\LabTechnicians\LabTechnicianResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLabTechnician extends EditRecord
{
    protected static string $resource = LabTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
