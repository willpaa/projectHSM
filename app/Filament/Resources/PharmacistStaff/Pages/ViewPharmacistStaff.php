<?php

namespace App\Filament\Resources\PharmacistStaff\Pages;

use App\Filament\Resources\PharmacistStaff\PharmacistStaffResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPharmacistStaff extends ViewRecord
{
    protected static string $resource = PharmacistStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
