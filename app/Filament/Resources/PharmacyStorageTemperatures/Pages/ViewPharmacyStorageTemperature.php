<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures\Pages;

use App\Filament\Resources\PharmacyStorageTemperatures\PharmacyStorageTemperatureResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPharmacyStorageTemperature extends ViewRecord
{
    protected static string $resource = PharmacyStorageTemperatureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url(PharmacyStorageTemperatureResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            EditAction::make(),
        ];
    }
}
