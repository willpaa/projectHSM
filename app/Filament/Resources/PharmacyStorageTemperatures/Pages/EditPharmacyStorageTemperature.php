<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures\Pages;

use App\Filament\Resources\PharmacyStorageTemperatures\PharmacyStorageTemperatureResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPharmacyStorageTemperature extends EditRecord
{
    protected static string $resource = PharmacyStorageTemperatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
