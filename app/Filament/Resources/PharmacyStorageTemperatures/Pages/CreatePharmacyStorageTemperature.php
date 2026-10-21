<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures\Pages;

use App\Filament\Resources\PharmacyStorageTemperatures\PharmacyStorageTemperatureResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePharmacyStorageTemperature extends CreateRecord
{
    protected static string $resource = PharmacyStorageTemperatureResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
}
