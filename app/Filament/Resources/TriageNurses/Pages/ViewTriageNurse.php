<?php

namespace App\Filament\Resources\TriageNurses\Pages;

use App\Filament\Resources\TriageNurses\TriageNurseResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTriageNurse extends ViewRecord
{
    protected static string $resource = TriageNurseResource::class;

    protected function getHeaderActions(): array
    {
        return [EditAction::make()];
    }
}
