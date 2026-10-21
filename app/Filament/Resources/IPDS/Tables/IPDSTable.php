<?php

namespace App\Filament\Resources\IPDS\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Beds;
use App\Models\Nurse;
use App\Models\IPD;
use App\Models\PatientHistory;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Staff;
use Filament\Forms\Components\Select;
use App\Support\ClinicalReportAccess;
use App\Support\ClinicalServiceRequestAction;
use App\Support\IPDServiceHeaderActions;
use App\Support\PatientPortal;
use App\Support\ClinicalNurseAccess;
use Illuminate\Auth\Access\AuthorizationException;
class IPDSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('encounter_id')
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('doctor.full_name')
                    ->searchable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('admission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('discharge_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('bed.id')
                    ->searchable(),
                TextColumn::make('prescription_id')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TableFilters::relationship('patient', 'full_name', 'Patient'),
                TableFilters::relationship('doctor', 'full_name', 'Doctor'),
                TableFilters::distinct('department', IPD::class, 'Department'),
                TableFilters::distinct('bed_id', IPD::class, 'Bed'),
                TableFilters::select('type', [

                    'IPD' => 'IPD',
                ]),
                TableFilters::select('status', [
                    'admitted' => 'Admitted',
                    'discharged' => 'Discharged',
                    'transferred' => 'Transferred',
                ]),
                TableFilters::dateRange('admission_date', 'Admission Date'),
                TableFilters::dateRange('discharge_date', 'Discharge Date'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                ClinicalServiceRequestAction::makeHeader('IPD'),
                IPDServiceHeaderActions::sendToLab(),
                IPDServiceHeaderActions::sendToRadiology(),
            ])

           ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (IPD $record): bool => ClinicalReportAccess::canViewReport($record)),
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
                            'diagnosis' => $record->diagnosis ?? '',
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
                Action::make('call_patient')
                    ->label('Call')
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->url(fn (IPD $record): ?string => filled($record->patient?->phone) ? 'tel:' . $record->patient->phone : null)
                    ->visible(fn (IPD $record): bool => ! PatientPortal::isPatientUser() && filled($record->patient?->phone)),

            ], RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords(fn (IPD $record): bool => ClinicalReportAccess::canViewReport($record)),
                ]),
            ]);
    }

    private static function authorizeAssignedDoctor(IPD $record): void
    {
        if (! ClinicalReportAccess::canViewReport($record)) {
            throw new AuthorizationException('Only the doctor assigned to this IPD encounter can perform this action.');
        }
    }
}
