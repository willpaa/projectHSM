<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures\Pages;

use App\Filament\Resources\PharmacyStorageTemperatures\PharmacyStorageTemperatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacyStorageTemperatures extends ListRecords
{
    protected static string $resource = PharmacyStorageTemperatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
