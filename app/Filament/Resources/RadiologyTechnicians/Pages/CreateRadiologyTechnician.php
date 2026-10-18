<?php

namespace App\Filament\Resources\RadiologyTechnicians\Pages;

use App\Filament\Resources\RadiologyTechnicians\RadiologyTechnicianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRadiologyTechnician extends CreateRecord
{
    protected static string $resource = RadiologyTechnicianResource::class;

    protected static bool $canCreateAnother = false;
}
