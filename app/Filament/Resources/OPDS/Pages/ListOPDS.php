<?php

namespace App\Filament\Resources\OPDS\Pages;

use App\Filament\Resources\OPDS\OPDResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListOPDS extends ListRecords
{
    protected static string $resource = OPDResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
