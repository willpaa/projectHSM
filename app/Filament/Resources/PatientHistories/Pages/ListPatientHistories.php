<?php

namespace App\Filament\Resources\PatientHistories\Pages;

use App\Filament\Resources\PatientHistories\PatientHistoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPatientHistories extends ListRecords
{
    protected static string $resource = PatientHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
