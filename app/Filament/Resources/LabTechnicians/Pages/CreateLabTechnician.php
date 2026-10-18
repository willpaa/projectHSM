<?php

namespace App\Filament\Resources\LabTechnicians\Pages;

use App\Filament\Resources\LabTechnicians\LabTechnicianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLabTechnician extends CreateRecord
{
    protected static string $resource = LabTechnicianResource::class;

    protected static bool $canCreateAnother = false;
}
