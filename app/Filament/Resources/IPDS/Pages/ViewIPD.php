<?php

namespace App\Filament\Resources\IPDS\Pages;

use App\Filament\Resources\IPDS\IPDResource;
use App\Models\Beds;
use App\Models\IPD;
use App\Support\ClinicalReportAccess;
use App\Support\PatientPortal;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
            Action::make('assign_bed')
                ->label('Assign Bed')
                ->icon('heroicon-o-building-office-2')
                ->color('primary')
                ->visible(fn (IPD $record): bool => ! PatientPortal::isPatientUser() && blank($record->bed_id) && in_array($record->status, ['admitted', 'transferred'], true))
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
                ])
                ->action(function (IPD $record, array $data): void {
                    $record->update([
                        'bed_id' => $data['bed_id'],
                        'status' => 'admitted',
                        'admission_date' => $record->admission_date ?? now()->toDateString(),
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
