<?php

namespace App\Filament\Resources\Beds\Pages;

use App\Filament\Resources\Beds\BedsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeds extends ListRecords
{
    protected static string $resource = BedsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
