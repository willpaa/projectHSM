<?php

namespace App\Filament\Resources\PharmacyMovements\Pages;

use App\Filament\Resources\PharmacyMovements\PharmacyMovementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPharmacyMovement extends EditRecord
{
    protected static string $resource = PharmacyMovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
