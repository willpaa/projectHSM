<?php

namespace App\Filament\Resources\Prescriptions\Pages;

use App\Filament\Resources\Prescriptions\PrescriptionResource;
use App\Support\ClinicalReportAccess;
use Filament\Resources\Pages\CreateRecord;

class CreatePrescription extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected static string $resource = PrescriptionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($doctorId = ClinicalReportAccess::currentDoctorId()) {
            $data['doctor_id'] = $doctorId;
        }

        return $data;
    }
}
