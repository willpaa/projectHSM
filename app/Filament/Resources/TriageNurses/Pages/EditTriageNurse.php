<?php

namespace App\Filament\Resources\TriageNurses\Pages;

use App\Filament\Resources\TriageNurses\TriageNurseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTriageNurse extends EditRecord
{
    protected static string $resource = TriageNurseResource::class;

    protected function getHeaderActions(): array
    {
        return [ViewAction::make(), DeleteAction::make()];
    }
}
