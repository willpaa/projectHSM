<?php

namespace App\Filament\Resources\Patients\Pages;

use App\Filament\Resources\Patients\PatientsResource;
use App\Services\PatientIdentifierGenerator;
use Filament\Resources\Pages\CreateRecord;
use App\Support\PatientPortalProvisioner;
use Filament\Notifications\Notification;

class CreatePatients extends CreateRecord
{
    protected static bool $canCreateAnother = false;
    protected bool $portalProvisioningFailed = false;

    public function mount(): void
    {
        parent::mount();

        $identifiers = app(PatientIdentifierGenerator::class)->next();
        $this->data = [
            ...($this->data ?? []),
            ...$identifiers,
        ];
        $this->form->fill($this->data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }
    protected static string $resource = PatientsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['patient_id'] ?? null) || blank($data['mrn'] ?? null)) {
            $identifiers = app(PatientIdentifierGenerator::class)->next();
            $data['patient_id'] = $data['patient_id'] ?: $identifiers['patient_id'];
            $data['mrn'] = $data['mrn'] ?: $identifiers['mrn'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            PatientPortalProvisioner::provision($this->record);
        } catch (\Throwable $exception) {
            $this->portalProvisioningFailed = true;
            report($exception);
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        $notification = Notification::make()
            ->title($this->portalProvisioningFailed
                ? 'Patient created; portal setup failed.'
                : 'Patient registered successfully.')
            ->body("Patient Code: {$this->record->patient_id} | MRN: {$this->record->mrn}");

        return $this->portalProvisioningFailed
            ? $notification->warning()
            : $notification->success();
    }
}
