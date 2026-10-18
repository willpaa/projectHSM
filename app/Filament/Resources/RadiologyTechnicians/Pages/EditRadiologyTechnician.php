<?php

namespace App\Filament\Resources\RadiologyTechnicians\Pages;

use App\Filament\Resources\RadiologyTechnicians\RadiologyTechnicianResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRadiologyTechnician extends EditRecord
{
    protected static string $resource = RadiologyTechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
