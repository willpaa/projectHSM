<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentsResource;
use App\Services\AppointmentSchedulingService;
use App\Support\PatientPortal;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAppointments extends CreateRecord
{
    protected static string $resource = AppointmentsResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AppointmentSchedulingService::class)->create(
            $data,
            (int) auth()->id(),
            PatientPortal::isPatientUser(),
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
}
