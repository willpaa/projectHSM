<?php

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Resources\Appointments\AppointmentsResource;
use App\Services\AppointmentSchedulingService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditAppointments extends EditRecord
{
    protected static string $resource = AppointmentsResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(AppointmentSchedulingService::class)->update($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cancel')
                ->label('Cancel appointment')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->record->status, ['requested', 'scheduled', 'checked_in'], true))
                ->action(function (AppointmentSchedulingService $scheduler): void {
                    $scheduler->transition($this->record, 'cancelled');
                    $this->redirect($this->getResourceUrl('index'));
                }),
        ];
    }
}
