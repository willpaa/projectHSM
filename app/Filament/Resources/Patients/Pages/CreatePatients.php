<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientsResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use App\Support\PatientPortalProvisioner;
use Filament\Notifications\Notification;

class CreatePatients extends CreateRecord
{
    protected static bool $canCreateAnother = false;
    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
    protected static string $resource = PatientsResource::class;
    protected function afterCreate(): void
    {
        try {
            $result = PatientPortalProvisioner::provision($this->record);

            Notification::make()
                ->title('Patient portal account created.')
                ->body($result['email_sent']
                    ? "Credentials were sent to {$result['email']}."
                    : "Email unavailable. Email: {$result['email']} | Temporary password: {$result['password']}")
                ->success()
                ->send();
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Patient created, but portal account provisioning failed.')
                ->warning()
                ->send();
        }
    }
}
