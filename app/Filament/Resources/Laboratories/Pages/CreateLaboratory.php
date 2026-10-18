<?php

namespace App\Filament\Resources\Laboratories\Pages;

use App\Filament\Resources\Laboratories\LaboratoryResource;
use App\Support\ClinicalReportAccess;
use Filament\Resources\Pages\CreateRecord;

class CreateLaboratory extends CreateRecord
{
        protected static bool $canCreateAnother=false;
    protected static string $resource = LaboratoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // A doctor can only ever request labs under their own doctor account.
        if (ClinicalReportAccess::isDoctorUser()) {
            $data['doctor_id'] = ClinicalReportAccess::currentDoctorId();
        }

        return $data;
    }
}

