<?php

namespace App\Filament\Resources\PharmacistStaff\Pages;

use App\Filament\Resources\PharmacistStaff\PharmacistStaffResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPharmacistStaff extends EditRecord
{
    protected static string $resource = PharmacistStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
