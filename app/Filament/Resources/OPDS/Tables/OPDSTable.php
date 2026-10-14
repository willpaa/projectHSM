<?php

namespace App\Filament\Resources\OPDS\Tables;

use App\Filament\Resources\IPDS\IPDResource;
use App\Filament\Support\TableFilters;
use App\Support\PatientPortal;
use App\Models\IPD;
use App\Models\Laboratory;
use App\Models\OPD;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\Doctors;
use App\Models\Service;
use App\Models\Staff;
use Filament\Forms\Components\Select;
use App\Models\Radiology;
class OPDSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('arrival_flag')
                    ->label('Arrival')
                    ->state(fn (OPD $record): string => $record->created_at?->isToday() ? 'New Today' : 'Old')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'New Today' ? 'success' : 'gray'),
                TextColumn::make('encounter_id')
                    ->searchable(),
                TextColumn::make('patient.patient_id')
                    ->label('Patient ID')
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient Name')
                    ->searchable(),
                TextColumn::make('patient.phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('doctor.full_name')
                    ->label('Doctor')
                    ->searchable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('icd10_code')
                    ->label('ICD-10')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('admission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('discharge_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('follow_up_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bed.bed_id')
                    ->label('Bed')
                    ->searchable(),
                TextColumn::make('prescription_id')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'New Patient',
                        'closed' => 'Closed',
                        'transferred' => 'Transferred',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'closed' => 'success',
                        'transferred' => 'info',
                        default => 'gray',
                    }),
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
                TableFilters::distinct('department', OPD::class, 'Department'),
                TableFilters::distinct('bed_id', OPD::class, 'Bed'),
                TableFilters::select('type', [
                    'OPD' => 'OPD',
                    'IPD' => 'IPD',
                ]),
                TableFilters::select('status', [
                    'open' => 'Open',
                    'closed' => 'Closed',
                    'transferred' => 'Transferred',
                ]),
                TableFilters::dateRange('admission_date', 'Admission Date'),
                TableFilters::dateRange('discharge_date', 'Discharge Date'),
                TableFilters::dateRange('follow_up_date', 'Follow-up Date'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                 Action::make('send_to_ipd')
                    ->label('Send to IPD')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn (OPD $record): bool => ! PatientPortal::isPatientUser() && $record->status === 'open')
                    ->form([
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->options(
                                Doctors::query()
                                    ->where('status', 'active')
                                    ->pluck('full_name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->required(),
                    ])
                     ->action(function (OPD $record, array $data): void {
                        $existingAdmission = IPD::query()
                            ->where('patient_id', $record->patient_id)
                            ->where('status', 'admitted')
                            ->exists();

                        if ( $existingAdmission) {
                            Notification::make()
                                ->title('Patient already has an open IPD encounter.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $encounterId = 'IPD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

                        IPD::create([
                            'encounter_id' => $encounterId,
                            'patient_id' => $record->patient_id,
                            'type' => 'IPD',
                            'doctor_id' => $data['doctor_id'],
                            'status' => 'admitted',
                        ]);

                        $record->update([
                            'status' => 'transferred',
                            'encounter_id' => $encounterId,
                        ]);

                        $ipdUsers = User::usersByRoleName('ipd_staff');
                        if ($ipdUsers->isNotEmpty()) {
                            Notification::make()
                                ->title('New IPD Queue')
                                ->body("Triage patient {$record->patient?->full_name} sent to IPD.")
                                ->icon('heroicon-o-bell-alert')
                                ->sendToDatabase($ipdUsers);
                        }

                        // Notify the doctor assigned to this IPD encounter so the
                        // patient appears in their account with a link to the record.
                        $assignedDoctor = Doctors::query()->with('user')->find($data['doctor_id']);
                        if ($assignedDoctor?->user) {
                            Notification::make()
                                ->title('New IPD Patient Assigned')
                                ->body("Patient {$record->patient?->full_name} has been admitted and assigned to you in IPD.")
                                ->icon('heroicon-o-user-plus')
                                ->actions([
                                    \Filament\Actions\Action::make('view')
                                        ->label('View Patient')
                                        ->url(IPDResource::getUrl('index')),
                                ])
                                ->sendToDatabase($assignedDoctor->user);
                        }
                    }),
                Action::make('call_patient')
                    ->label('Call')
                    ->icon('heroicon-o-phone')
                    ->color('success')
                    ->url(fn (OPD $record): ?string => filled($record->patient?->phone) ? 'tel:' . $record->patient->phone : null)
                    ->visible(fn (OPD $record): bool => ! PatientPortal::isPatientUser() && filled($record->patient?->phone)),
                Action::make('send_to_lab')
                    ->label('Send to Lab')
                    ->icon('heroicon-o-beaker')
                    ->color('warning')
                    ->visible(fn (OPD $record): bool => ! PatientPortal::isPatientUser() && filled($record->patient_id) && filled($record->doctor_id))
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
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->searchable()
                            ->preload()
                            ->placeholder('Assign a laboratorist (optional)'),
                    ])
                    ->action(function (OPD $record, array $data): void {
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
                            'encounter_type' => 'OPD',
                            'encounter_id' => $record->encounter_id,
                            'test_name' => $data['test_name'],
                            'test_type' => $data['test_type'] ?? 'General',
                            'sample_type' => $data['sample_type'] ?? 'Blood',
                            'test_date' => now()->toDateString(),
                            'result_status' => 'pending',
                            'status' => 'ordered',
                            'cost' => $service?->price ?? 0,
                        ]);

                        $labUsers = User::usersByRoleName('lab_technician');
                        if ($labUsers->isNotEmpty()) {
                            $body = "OPD patient {$record->patient?->full_name} sent to Lab.";
                            if ($assignedLaboratorist) {
                                $body .= " Assigned laboratorist: {$assignedLaboratorist->full_name}.";
                            }

                            Notification::make()
                                ->title('New Laboratory Queue')
                                ->body($body)
                                ->icon('heroicon-o-bell-alert')
                                ->sendToDatabase($labUsers);
                        }

                        Notification::make()
                            ->title('Patient sent to Laboratory.')
                            ->body("Lab ID: {$labId}. The laboratorist will send the request to cashier after review.")
                            ->success()
                            ->send();
                    }),
                Action::make('discharge_patient')
                    ->label('Discharge')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (OPD $record): bool => ! PatientPortal::isPatientUser() && $record->status === 'open')
                    ->form([
                        Textarea::make('discharge_summary')
                            ->label('Discharge Summary')
                            ->required()
                            ->rows(3),
                        DatePicker::make('follow_up_date')
                            ->label('Follow-up Date'),
                    ])
                    ->action(function (OPD $record, array $data): void {
                        $record->update([
                            'status' => 'closed',
                            'discharge_date' => now()->toDateString(),
                            'discharge_summary' => $data['discharge_summary'],
                            'follow_up_date' => $data['follow_up_date'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Patient discharged from OPD.')
                            ->success()
                            ->send();
                    }),
                
                Action::make('send_to_radiology')
                    ->label('Send to Radiology')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->visible(fn (OPD $record): bool => ! PatientPortal::isPatientUser() && $record->status === 'open')
                    ->form([
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->options(
                                Doctors::query()
                                    ->where('status', 'active')
                                    ->pluck('full_name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->required(),
                        Textarea::make('exam_name')
                            ->label('Exam')
                            ->default('OPD Requested Imaging')
                            ->required()
                            ->rows(2),
                    ])
                    
                    ->action(function (OPD $record, array $data): void {
                        $radiologyId = 'RAD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
                        Radiology::create([
                            'radiology_id' => $radiologyId,
                            'patient_id' => $record->patient_id,
                            'doctor_id' => $data['doctor_id'],
                            'encounter_type' => 'OPD',
                            'encounter_id' => $record->encounter_id,
                            'exam_name' => $data['exam_name'],
                            'modality' => 'X-Ray',
                            'exam_date' => now()->toDateString(),
                            'result_status' => 'pending',
                            'status' => 'ordered',
                        ]);

                        

                        $radiologyUsers = User::usersByRoleName('radiology_technician');
                        if ($radiologyUsers->isNotEmpty()) {
                            Notification::make()
                                ->title('New Radiology Queue')
                                ->body("OPD patient {$record->patient?->full_name} sent to Radiology.")
                                ->icon('heroicon-o-bell-alert')
                                ->sendToDatabase($radiologyUsers);
                        }

                        Notification::make()
                            ->title('Patient sent to Radiology.')
                            ->body("Radiology ID: {$radiologyId}. The radiologist will send the request to cashier after review.")
                            ->success()
                            ->send();
             }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
