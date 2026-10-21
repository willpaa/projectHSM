<?php

namespace App\Filament\Resources\PharmacistStaff\Pages;

use App\Filament\Resources\PharmacistStaff\PharmacistStaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePharmacistStaff extends CreateRecord
{
    protected static string $resource = PharmacistStaffResource::class;

    protected static bool $canCreateAnother = false;
}
