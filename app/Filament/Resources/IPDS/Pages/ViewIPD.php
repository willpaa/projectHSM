<?php

namespace App\Filament\Resources\IPDS\Pages;

use App\Filament\Resources\IPDS\IPDResource;
use App\Models\Beds;
use App\Models\IPD;
use App\Models\Nurse;
use App\Models\ServiceRequest;
use App\Support\ClinicalReportAccess;
use App\Support\ClinicalNurseAccess;
use App\Support\PatientPortal;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Auth\Access\AuthorizationException;

class ViewIPD extends ViewRecord
{
    protected static string $resource = IPDResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url(IPDResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Action::make('record_nursing_observation')
                ->label('Record Nursing Observation')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->visible(fn (IPD $record): bool => ClinicalNurseAccess::isNurse()
                    && in_array($record->status, ['admitted'], true)
                    && ClinicalNurseAccess::currentNurseId('nurse') !== null
                    && (int) $record->bed?->assigned_nurse_id === ClinicalNurseAccess::currentNurseId('nurse'))
                ->form([
                    DateTimePicker::make('observed_at')
                        ->label('Observed At')
                        ->default(now())
                        ->required(),
                    TextInput::make('temperature')->label('Temperature (°C)')->numeric(),
                    TextInput::make('blood_pressure')->label('Blood Pressure')->placeholder('120/80'),
                    TextInput::make('pulse_rate')->label('Pulse Rate')->numeric(),
                    TextInput::make('respiratory_rate')->label('Respiratory Rate')->numeric(),
                    TextInput::make('oxygen_saturation')->label('Oxygen Saturation (%)')->numeric(),
                    Textarea::make('nursing_note')
                        ->label('Nursing Care / Observation')
                        ->rows(4)
                        ->required()
                        ->columnSpanFull(),
                ])
                ->action(function (IPD $record, array $data): void {
                    $nurseId = ClinicalNurseAccess::currentNurseId('nurse');

                    if ($nurseId === null || (int) $record->bed?->assigned_nurse_id !== $nurseId) {
                        throw new AuthorizationException('You can only record nursing observations for patients assigned to you.');
                    }

                    $details = array_filter([
                        'Observed at' => $data['observed_at'],
                        'Temperature' => $data['temperature'] ?? null,
                        'Blood pressure' => $data['blood_pressure'] ?? null,
                        'Pulse' => $data['pulse_rate'] ?? null,
                        'Respiratory rate' => $data['respiratory_rate'] ?? null,
                        'Oxygen saturation' => $data['oxygen_saturation'] ?? null,
                        'Nurse' => auth()->user()->name,
                        'Care note' => trim($data['nursing_note']),
                    ], fn (?string $value): bool => filled($value));
                    $observation = implode(' | ', array_map(
                        fn (string $label, string $value): string => "{$label}: {$value}",
                        array_keys($details),
                        array_values($details),
                    ));

                    $record->update([
                        'nursing_notes' => trim(implode(PHP_EOL . PHP_EOL, array_filter([
                            $record->nursing_notes,
                            $observation,
                        ]))),
                    ]);

                    Notification::make()
                        ->title('Nursing observation saved.')
                        ->success()
                        ->send();
                }),
            Action::make('update_service_care')
                ->label('Update Requested Service')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn (IPD $record): bool => ClinicalNurseAccess::isNurse()
                    && $record->status === 'admitted'
                    && ClinicalNurseAccess::currentNurseId('nurse') !== null
                    && (int) $record->bed?->assigned_nurse_id === ClinicalNurseAccess::currentNurseId('nurse'))
                ->form(fn (IPD $record): array => [
                    Select::make('service_request_id')
                        ->label('Paid service request')
                        ->options(fn (): array => $record->serviceRequests()
                            ->whereIn('payment_status', ['paid', 'insurance', 'waived'])
                            ->whereNotIn('fulfillment_status', ['completed', 'cancelled'])
                            ->with('service')
                            ->get()
                            ->mapWithKeys(fn (ServiceRequest $request): array => [
                                $request->id => "{$request->request_number} — {$request->service?->name} ({$request->fulfillment_status})",
                            ])
                            ->all())
                        ->searchable()
                        ->required(),
                    Select::make('fulfillment_status')
                        ->label('Care status')
                        ->options([
                            'in_progress' => 'In progress',
                            'completed' => 'Completed',
                        ])
                        ->required(),
                    Textarea::make('notes')
                        ->label('Nursing service note')
                        ->rows(3)
                        ->required()
                        ->maxLength(2000),
                ])
                ->action(function (IPD $record, array $data): void {
                    $nurseId = ClinicalNurseAccess::currentNurseId('nurse');

                    if ($nurseId === null || (int) $record->bed?->assigned_nurse_id !== $nurseId) {
                        throw new AuthorizationException('You can only update services for a patient assigned to you.');
                    }

                    $request = $record->serviceRequests()
                        ->whereKey($data['service_request_id'])
                        ->whereIn('payment_status', ['paid', 'insurance', 'waived'])
                        ->whereNotIn('fulfillment_status', ['completed', 'cancelled'])
                        ->firstOrFail();

                    $request->update([
                        'fulfillment_status' => $data['fulfillment_status'],
                        'notes' => trim(implode(PHP_EOL . PHP_EOL, array_filter([
                            $request->notes,
                            'Nursing update by ' . auth()->user()->name . ': ' . trim($data['notes']),
                        ]))),
                    ]);

                    Notification::make()
                        ->title('Requested service updated.')
                        ->success()
                        ->send();
                }),
            Action::make('assign_bed')
                ->label('Assign Bed')
                ->icon('heroicon-o-building-office-2')
                ->color('primary')
                ->visible(fn (IPD $record): bool => ! PatientPortal::isPatientUser()
                    && ! ClinicalNurseAccess::isNurse()
                    && ! ClinicalNurseAccess::isTriageNurse()
                    && blank($record->bed_id)
                    && in_array($record->status, ['admitted', 'transferred'], true))
                ->form([
                    Select::make('bed_id')
                        ->label('Bed')
                        ->options(function (): array {
                            return Beds::query()
                                ->where(function ($query): void {
                                    $query->whereIn('occupancy_status', ['available', 'reserved'])
                                        ->orWhereNull('current_patient_id');
                                })
                                ->orderBy('ward_name')
                                ->orderBy('bed_no')
                                ->get()
                                ->mapWithKeys(fn (Beds $bed): array => [
                                    $bed->bed_id => sprintf('%s - %s (%s)', $bed->ward_name, $bed->bed_no, $bed->bed_type),
                                ])
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                    Select::make('assigned_nurse_id')
                        ->label('Responsible Nurse')
                        ->options(fn (): array => Nurse::query()
                            ->where('role', 'nurse')
                            ->where('status', 'active')
                            ->orderBy('full_name')
                            ->pluck('full_name', 'id')
                            ->all())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (IPD $record, array $data): void {
                    $record->update([
                        'bed_id' => $data['bed_id'],
                        'status' => 'admitted',
                        'admission_date' => $record->admission_date ?? now()->toDateString(),
                    ]);
                    Beds::query()->where('bed_id', $data['bed_id'])->update([
                        'assigned_nurse_id' => $data['assigned_nurse_id'],
                    ]);

                    Notification::make()
                        ->title('Bed assigned successfully.')
                        ->body("Patient {$record->patient?->full_name} assigned to bed {$data['bed_id']}.")
                        ->success()
                        ->send();
                }),
            Action::make('discharge_patient')
                ->label('Discharge Patient')
                ->icon('heroicon-o-arrow-left-start-on-rectangle')
                ->color('danger')
                ->visible(fn (IPD $record): bool => ! PatientPortal::isPatientUser() && ClinicalReportAccess::canViewReport($record)
                    && filled($record->bed_id)
                    && ! in_array($record->status, ['discharged', 'transferred'], true))
                ->form([
                    DatePicker::make('discharge_date')
                        ->label('Discharge Date')
                        ->default(now())
                        ->required(),
                    Textarea::make('discharge_summary')
                        ->label('Discharge Notes')
                        ->rows(3)
                        ->nullable(),
                ])
                ->action(function (IPD $record, array $data): void {
                    self::authorizeAssignedDoctor($record);

                    $record->update([
                        'status' => 'discharged',
                        'discharge_date' => $data['discharge_date'],
                    ]);

                    if (! empty($data['discharge_summary'])) {
                        $record->update([
                            'diagnosis' => trim($record->diagnosis . PHP_EOL . PHP_EOL . $data['discharge_summary']),
                        ]);
                    }

                    Notification::make()
                        ->title('Patient discharged.')
                        ->body("Bed {$record->bed_id} has been released for {$record->patient?->full_name}.")
                        ->success()
                        ->send();
                }),
            EditAction::make(),
            DeleteAction::make()
                ->visible(fn (IPD $record): bool => ClinicalReportAccess::canViewReport($record)),
        ];
    }

    private static function authorizeAssignedDoctor(IPD $record): void
    {
        if (! ClinicalReportAccess::canViewReport($record)) {
            throw new AuthorizationException('Only the doctor assigned to this IPD encounter can discharge this patient.');
        }
    }
}
