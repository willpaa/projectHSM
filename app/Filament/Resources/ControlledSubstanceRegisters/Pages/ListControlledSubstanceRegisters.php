<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters\Pages;

use App\Filament\Resources\ControlledSubstanceRegisters\ControlledSubstanceRegisterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListControlledSubstanceRegisters extends ListRecords
{
    protected static string $resource = ControlledSubstanceRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Record Movement'),
        ];
    }
}
