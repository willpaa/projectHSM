<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters\Pages;

use App\Filament\Resources\ControlledSubstanceRegisters\ControlledSubstanceRegisterResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewControlledSubstanceRegister extends ViewRecord
{
    protected static string $resource = ControlledSubstanceRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url(ControlledSubstanceRegisterResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
