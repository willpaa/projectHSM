<?php

namespace App\Filament\Resources\PharmacyMovements\Pages;

use App\Filament\Resources\PharmacyMovements\PharmacyMovementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacyMovements extends ListRecords
{
    protected static string $resource = PharmacyMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
