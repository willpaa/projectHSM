<?php

namespace App\Filament\Resources\PharmacistStaff\Pages;

use App\Filament\Resources\PharmacistStaff\PharmacistStaffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPharmacistStaff extends ListRecords
{
    protected static string $resource = PharmacistStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
