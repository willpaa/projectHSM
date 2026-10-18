<?php

namespace App\Filament\Resources\IPDS\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Beds;
use App\Models\IPD;
use App\Models\Laboratory;
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
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctors;
use App\Models\Service;
use App\Models\Staff;
use Filament\Forms\Components\Select;
use App\Models\Radiology;
use App\Support\ClinicalReportAccess;
use App\Support\PatientPortal;
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

           ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (IPD $record): bool => ClinicalReportAccess::canViewReport($record)),
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
                Action::make('send_to_lab')
                    ->label('Send to Lab')
                    ->icon('heroicon-o-beaker')
                    ->color('warning')
                    ->visible(fn (IPD $record): bool => ClinicalReportAccess::canViewReport($record)
                        && $record->status !== 'discharged'
                        && filled($record->patient_id)
                        && filled($record->doctor_id))
                    ->form([
                        Select::make('service_id')
                            ->label('Lab Service / Type')
                            ->options(fn (): array => Service::query()
                                ->where('service_type', 'lab')
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                $service = $state ? Service::find($state) : null;

                                if ($service) {
                                    $set('test_name', $service->name);
                                    $set('cost', (float) $service->price);
                                }
                            })
                            ->required()
                            ->helperText('Choose the type of lab service the doctor is requesting.'),
                        Textarea::make('test_name')
                            ->label('Test / Panel')
                            ->required()
                            ->rows(2)
                            ->helperText('Specific test or panel name for this request.'),
                        Select::make('test_type')
                            ->label('Test Category')
                            ->options([
                                'Hematology' => 'Hematology',
                                'Biochemistry' => 'Biochemistry',
                                'Microbiology' => 'Microbiology',
                                'Serology' => 'Serology',
                                'Immunology' => 'Immunology',
                                'Pathology' => 'Pathology',
                                'Endocrinology' => 'Endocrinology',
                                'General' => 'General',
                            ])
                            ->default('General')
                            ->required(),
                        Select::make('sample_type')
                            ->label('Sample Type')
                            ->options([
                                'Blood' => 'Blood',
                                'Urine' => 'Urine',
                                'Stool' => 'Stool',
                                'Sputum' => 'Sputum',
                                'Swab' => 'Swab',
                                'Tissue' => 'Tissue',
                                'Other' => 'Other',
                            ])
                            ->default('Blood')
                            ->required(),
                        Select::make('laboratorist_id')
                            ->label('Laboratorist (Lab Technician)')
                            ->options(fn (): array => Staff::query()
                                ->where('role', 'lab_technician')
                                ->where('status', 'active')
                                ->whereNotNull('user_id')
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->placeholder('Assign a laboratorist')
                            ->required(),
                    ])
                    ->action(function (IPD $record, array $data): void {
                        self::authorizeAssignedDoctor($record);

                        if ($record->status === 'discharged') {
                            Notification::make()
                                ->title('Cannot send a lab request for a discharged IPD patient.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $pendingLabExists = Laboratory::query()
                            ->where('patient_id', $record->patient_id)
                            ->where('doctor_id', $record->doctor_id)
                            ->where('encounter_id', $record->encounter_id)
                            ->whereIn('status', ['ordered', 'sample_collected', 'processing'])
                            ->exists();

                        if ($pendingLabExists) {
                            Notification::make()
                                ->title('Lab request already exists for this patient.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $labId = 'LAB-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
                        $service = ! empty($data['service_id']) ? Service::find($data['service_id']) : null;
                        $assignedLaboratorist = ! empty($data['laboratorist_id']) ? Staff::find($data['laboratorist_id']) : null;

                        Laboratory::create([
                            'lab_id' => $labId,
                            'patient_id' => $record->patient_id,
                            'doctor_id' => $record->doctor_id,
                            'laboratorist_id' => $data['laboratorist_id'] ?? null,
                            'encounter_type' => 'IPD',
                            'encounter_id' => $record->encounter_id,
                            'test_name' => $data['test_name'],
                            'test_type' => $data['test_type'] ?? 'General',
                            'sample_type' => $data['sample_type'] ?? 'Blood',
                            'test_date' => now()->toDateString(),
                            'result_status' => 'pending',
                            'status' => 'ordered',
                            'cost' => $service?->price ?? 0,
                        ]);

                        if ($assignedLaboratorist?->user) {
                            $body = "IPD patient {$record->patient?->full_name} sent to Lab.";
                            $body .= " Assigned laboratorist: {$assignedLaboratorist->full_name}.";

                            Notification::make()
                                ->title('New Laboratory Queue')
                                ->body($body)
                                ->icon('heroicon-o-bell-alert')
                                ->sendToDatabase(collect([$assignedLaboratorist->user]));
                        }

                        Notification::make()
                            ->title('Patient sent to Laboratory.')
                            ->body("Lab ID: {$labId}. The laboratorist will send the request to cashier after review.")
                            ->success()
                            ->send();
                    }),
                 Action::make('send_to_radiology')
                    ->label('Send to Radiology')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->visible(fn (IPD $record): bool => ClinicalReportAccess::canViewReport($record)
                        && $record->status === 'admitted')
                    ->form([
                       
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->options(fn (IPD $record): array => Doctors::query()
                                ->whereKey($record->doctor_id)
                                ->pluck('full_name', 'id')
                                ->toArray())
                            ->searchable()
                            ->default(fn (IPD $record): ?int => (int) $record->doctor_id)
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        Select::make('radiologist_id')
                            ->label('Radiology Technician')
                            ->options(fn (): array => Staff::query()
                                ->where('role', 'radiology_technician')
                                ->where('status', 'active')
                                ->whereNotNull('user_id')
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        Textarea::make('exam_name')
                            ->label('Exam')
                            ->default('IPD Requested Imaging')
                            ->required()
                            ->rows(2),
                    ])

                    ->action(function (IPD $record, array $data): void {
                        self::authorizeAssignedDoctor($record);

                        if ($record->status === 'discharged') {
                            Notification::make()
                                ->title('Cannot send a radiology request for a discharged IPD patient.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $radiologyId = 'RAD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
                        $assignedRadiologist = Staff::query()->with('user')->find($data['radiologist_id']);
                        Radiology::create([
                            'radiology_id' => $radiologyId,
                            'patient_id' => $record->patient_id,
                            'doctor_id' => $record->doctor_id,
                            'radiologist_id' => $data['radiologist_id'],
                            'encounter_type' => 'IPD',
                            'encounter_id' => $record->encounter_id,
                            'exam_name' => $data['exam_name'],
                            'modality' => 'X-Ray',
                            'exam_date' => now()->toDateString(),
                            'result_status' => 'pending',
                            'status' => 'ordered',
                        ]);



                        if ($assignedRadiologist?->user) {
                            Notification::make()
                                ->title('New Radiology Queue')
                                ->body("IPD patient {$record->patient?->full_name} assigned to {$assignedRadiologist->full_name}.")
                                ->icon('heroicon-o-bell-alert')
                                ->sendToDatabase(collect([$assignedRadiologist->user]));
                        }

                        Notification::make()
                            ->title('Patient sent to Radiology.')
                            ->body("Radiology ID: {$radiologyId}. The radiologist will send the request to cashier after review.")
                            ->success()
                            ->send();
             }),

            ])
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
