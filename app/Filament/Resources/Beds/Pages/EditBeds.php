<?php

namespace App\Filament\Resources\Beds\Pages;

use App\Filament\Resources\Beds\BedsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBeds extends EditRecord
{
    protected static string $resource = BedsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
