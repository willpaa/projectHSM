<?php

namespace App\Filament\Resources\TriageNurses\Pages;

use App\Filament\Resources\TriageNurses\TriageNurseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTriageNurse extends CreateRecord
{
    protected static string $resource = TriageNurseResource::class;

    protected static bool $canCreateAnother = false;
}
