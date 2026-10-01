<?php

namespace App\Filament\Resources\PatientHistories\Pages;

use App\Filament\Resources\PatientHistories\PatientHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePatientHistory extends CreateRecord
{
    protected static string $resource = PatientHistoryResource::class;
}
