<?php

namespace App\Filament\Resources\TriageNurses\Pages;

use App\Filament\Resources\TriageNurses\TriageNurseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTriageNurses extends ListRecords
{
    protected static string $resource = TriageNurseResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
