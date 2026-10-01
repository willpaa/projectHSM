<?php

namespace App\Filament\Resources\PatientHistories\Pages;

use App\Filament\Resources\PatientHistories\PatientHistoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPatientHistory extends EditRecord
{
    protected static string $resource = PatientHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
