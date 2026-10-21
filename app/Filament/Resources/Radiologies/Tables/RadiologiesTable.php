<?php

namespace App\Filament\Resources\Radiologies\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Radiology;
use App\Models\RadiologyReportRevision;
use App\Support\ClinicalReportAccess;
use App\Support\ClinicalStaffAccess;
use App\Support\ClinicalResultNotifier;
use App\Support\PatientPortal;
use App\Support\RadiologyHeaderActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class RadiologiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('radiology_id')
                    ->searchable(),
                TextColumn::make('patient.patient_id')
                    ->label('Patient ID')
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(),
                TextColumn::make('doctor.full_name')
                    ->label('Doctor')
                    ->searchable(),
                TextColumn::make('radiologist.full_name')
                    ->label('Assigned Radiology Technician')
                    ->searchable()
                    ->placeholder('Unassigned'),
                TextColumn::make('exam_name')
                    ->searchable(),
                TextColumn::make('modality')
                    ->searchable(),
                TextColumn::make('body_part')
                    ->label('Body Part')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('urgency')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'emergency' => 'danger',
                        'urgent' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('clinical_indication')
                    ->label('Clinical Indication')
                    ->limit(60)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('result_status')
                    ->badge()
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isRadiologyStaff()),
                TextColumn::make('result_summary')
                    ->label('Result Summary')
                    ->limit(60)
                    ->wrap()
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isRadiologyStaff()),
                TextColumn::make('serviceRequest.payment_status')
                    ->label('Payment')
                    ->badge()
                    ->default('not sent')
                    ->color(fn (?string $state): string => match ($state) {
                        'paid', 'insurance', 'waived' => 'success',
                        'verified' => 'info',
                        'cancelled' => 'danger',
                        null, 'not sent' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TableFilters::relationship('patient', 'full_name', 'Patient'),
                TableFilters::relationship('doctor', 'full_name', 'Doctor'),
                TableFilters::relationship('radiologist', 'full_name', 'Radiology Technician')
                    ->visible(fn (): bool => ! ClinicalStaffAccess::isRadiologyStaff()),
                TableFilters::distinct('exam_name', Radiology::class, 'Exam Name'),
                TableFilters::distinct('modality', Radiology::class, 'Modality'),
                TableFilters::select('result_status', [
                    'normal' => 'Normal',
                    'abnormal' => 'Abnormal',
                    'critical' => 'Critical',
                    'pending' => 'Pending',
                ])->visible(fn (): bool => ClinicalReportAccess::isDoctorUser()),
                TableFilters::select('status', [
                    'ordered' => 'Ordered',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'cancelled' => 'Cancelled',
                ]),
                TableFilters::dateRange('exam_date', 'Exam Date'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->modifyQueryUsing(fn ($query) => $query->with(['patient', 'doctor', 'radiologist', 'serviceRequest.service']))
            ->headerActions([
                RadiologyHeaderActions::sendToCashier(),
                RadiologyHeaderActions::resendToCashier(),
            ])
            ->recordActions([

                Action::make('submit_result')
    ->label('Submit Result')
    ->icon('heroicon-o-check-circle')
    ->color('success')
    ->hidden(fn ($record) => $record->status === 'completed' || ! $record->paymentCleared())
    ->visible(fn (Radiology $record): bool => ! PatientPortal::isPatientUser()
        && (ClinicalStaffAccess::canManageRadiology($record)
            || (auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false)))
    ->form([
        FileUpload::make('report_image')
            ->label('Upload Supporting Image (Optional)')
            ->directory('radiology-reports')
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(10240),
        Textarea::make('result_summary')
            ->label('Result Summary')
            ->rows(3),
        Textarea::make('report_technique')
            ->label('Technique / Procedure')
            ->helperText('Record the imaging technique or procedure performed, including contrast details when relevant.')
            ->rows(3),
        Textarea::make('comparison_studies')
            ->label('Comparison with Prior Studies')
            ->helperText('Add prior study/date when available, or state that no comparison study is available.')
            ->rows(3),
        Textarea::make('findings')
            ->label('Radiology Findings')
            ->required()
            ->rows(5),
        Select::make('result_status')
            ->label('Result Assessment')
            ->options([
                'normal' => 'Normal',
                'abnormal' => 'Abnormal',
                'critical' => 'Critical',
            ])
            ->live()
            ->required(),
        Textarea::make('conclusion')
            ->label('Conclusion/Recommendation')
            ->required(),
        TextInput::make('critical_contact_name')
            ->label('Critical Finding Communicated To')
            ->helperText('For critical findings, contact the responsible clinician directly under local policy; an in-app alert alone may not be enough.')
            ->visible(fn (Get $get): bool => $get('result_status') === 'critical')
            ->required(fn (Get $get): bool => $get('result_status') === 'critical'),
        Select::make('critical_contact_method')
            ->label('Critical Finding Communication Method')
            ->options(['phone' => 'Phone', 'in_person' => 'In person', 'secure_message' => 'Secure message', 'other' => 'Other'])
            ->visible(fn (Get $get): bool => $get('result_status') === 'critical')
            ->required(fn (Get $get): bool => $get('result_status') === 'critical'),
        DateTimePicker::make('critical_contacted_at')
            ->label('Critical Finding Communication Time')
            ->seconds(false)
            ->maxDate(now())
            ->visible(fn (Get $get): bool => $get('result_status') === 'critical')
            ->required(fn (Get $get): bool => $get('result_status') === 'critical'),
    ])
    ->action(function (Radiology $record, array $data): void {
        abort_unless(
            ClinicalStaffAccess::canManageRadiology($record)
                || (auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false),
            403,
        );

        $reportData = self::reportData($data);
        $saved = DB::transaction(function () use ($record, $reportData): bool {
            $lockedRecord = Radiology::query()->lockForUpdate()->findOrFail($record->getKey());

            abort_if($lockedRecord->status === 'completed', 409, 'A completed result cannot be submitted again.');

            if (! $lockedRecord->paymentCleared()) {
                return false;
            }

            $lockedRecord->update(array_merge($reportData, [
                'status' => 'completed',
                'completed_at' => now(),
            ]));
            $lockedRecord->serviceRequest?->update(['fulfillment_status' => 'completed']);
            $lockedRecord->reportRevisions()->create([
                'revision_number' => 1,
                'revision_type' => 'initial',
                'report_data' => $reportData,
                'authored_by' => auth()->id(),
            ]);

            return true;
        });

        if (! $saved) {
            Notification::make()
                ->title('Payment is not cleared.')
                ->body('Send the patient to cashier and wait until payment is completed.')
                ->danger()
                ->send();

            return;
        }

        if (ClinicalResultNotifier::notifyDoctor($record->refresh())) {
            Notification::make()
                ->title('Result submitted and doctor notified.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Result saved, but the doctor was not notified.')
                ->body('Link a user account to the assigned doctor, then resend the notification.')
                ->warning()
                ->send();
        }
    }),
                Action::make('amend_result')
                    ->label('Addendum / Correct Result')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('This creates a new version and keeps the previous report in the audit history. Explain why the report is being amended.')
                    ->visible(fn (Radiology $record): bool => $record->status === 'completed'
                        && (ClinicalStaffAccess::canManageRadiology($record)
                            || (auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false)))
                    ->form([
                        Select::make('revision_type')
                            ->label('Change Type')
                            ->options(['correction' => 'Correction', 'addendum' => 'Addendum'])
                            ->required(),
                        Textarea::make('reason')->label('Reason for Change')->required()->rows(3),
                        Textarea::make('result_summary')->label('Result Summary')->default(fn (Radiology $record) => $record->result_summary)->rows(3),
                        Textarea::make('report_technique')->label('Technique / Procedure')->default(fn (Radiology $record) => $record->report_technique)->rows(3),
                        Textarea::make('comparison_studies')->label('Comparison with Prior Studies')->default(fn (Radiology $record) => $record->comparison_studies)->rows(3),
                        Textarea::make('findings')->label('Radiology Findings')->default(fn (Radiology $record) => $record->findings)->required()->rows(5),
                        Select::make('result_status')
                            ->label('Result Assessment')
                            ->options(['normal' => 'Normal', 'abnormal' => 'Abnormal', 'critical' => 'Critical'])
                            ->default(fn (Radiology $record) => $record->result_status)
                            ->live()
                            ->required(),
                        Textarea::make('conclusion')->label('Conclusion / Recommendation')->default(fn (Radiology $record) => $record->conclusion)->required(),
                        FileUpload::make('report_image')
                            ->label('Supporting Image (Optional)')
                            ->directory('radiology-reports')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(10240)
                            ->default(fn (Radiology $record) => $record->report_image),
                        TextInput::make('critical_contact_name')
                            ->label('Critical Finding Communicated To')
                            ->visible(fn (Get $get): bool => $get('result_status') === 'critical')
                            ->required(fn (Get $get): bool => $get('result_status') === 'critical'),
                        Select::make('critical_contact_method')
                            ->label('Critical Finding Communication Method')
                            ->options(['phone' => 'Phone', 'in_person' => 'In person', 'secure_message' => 'Secure message', 'other' => 'Other'])
                            ->visible(fn (Get $get): bool => $get('result_status') === 'critical')
                            ->required(fn (Get $get): bool => $get('result_status') === 'critical'),
                        DateTimePicker::make('critical_contacted_at')
                            ->label('Critical Finding Communication Time')
                            ->seconds(false)
                            ->maxDate(now())
                            ->visible(fn (Get $get): bool => $get('result_status') === 'critical')
                            ->required(fn (Get $get): bool => $get('result_status') === 'critical'),
                    ])
                    ->action(function (Radiology $record, array $data): void {
                        abort_unless(
                            ClinicalStaffAccess::canManageRadiology($record)
                                || (auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false),
                            403,
                        );

                        $reportData = self::reportData($data);
                        DB::transaction(function () use ($record, $data, $reportData): void {
                            $lockedRecord = Radiology::query()->lockForUpdate()->findOrFail($record->getKey());
                            abort_unless($lockedRecord->status === 'completed', 409, 'Only completed reports can be amended.');

                            $revisionNumber = ((int) $lockedRecord->reportRevisions()->max('revision_number')) + 1;

                            if ($revisionNumber === 1) {
                                $lockedRecord->reportRevisions()->create([
                                    'revision_number' => 1,
                                    'revision_type' => 'initial',
                                    'reason' => 'Existing report archived before its first amendment.',
                                    'report_data' => self::reportData($lockedRecord->getAttributes()),
                                ]);
                                $revisionNumber = 2;
                            }

                            $lockedRecord->update(array_merge($reportData, [
                                'completed_at' => now(),
                                'reviewed_at' => null,
                                'reviewed_by' => null,
                                'doctor_review_notes' => null,
                            ]));
                            $lockedRecord->reportRevisions()->create([
                                'revision_number' => $revisionNumber,
                                'revision_type' => $data['revision_type'],
                                'reason' => $data['reason'],
                                'report_data' => $reportData,
                                'authored_by' => auth()->id(),
                            ]);
                        });

                        if (ClinicalResultNotifier::notifyDoctor($record->refresh())) {
                            Notification::make()->title('Updated report saved and doctor notified.')->success()->send();
                        } else {
                            Notification::make()->title('Updated report saved, but the doctor was not notified.')
                                ->body('Link a user account to the assigned doctor, then resend the notification.')
                                ->warning()->send();
                        }
                    }),
                Action::make('doctor_review')
                    ->label(fn (Radiology $record): string => $record->reviewed_at ? 'Reviewed' : 'Acknowledge Review')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('success')
                    ->modalHeading('Review Radiology Report')
                    ->modalDescription('Record that you reviewed the current report version. Any later correction/addendum will require another review.')
                    ->visible(fn (Radiology $record): bool => $record->status === 'completed'
                        && ClinicalReportAccess::canViewReport($record))
                    ->disabled(fn (Radiology $record): bool => $record->reviewed_at !== null)
                    ->form([
                        Textarea::make('doctor_review_notes')
                            ->label('Review Notes (Optional)')
                            ->rows(3),
                    ])
                    ->action(function (Radiology $record, array $data): void {
                        abort_unless(ClinicalReportAccess::canViewReport($record), 403);
                        abort_if($record->status !== 'completed', 409, 'Only completed reports can be reviewed.');

                        $record->update([
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                            'doctor_review_notes' => $data['doctor_review_notes'] ?? null,
                        ]);

                        Notification::make()->title('Report review recorded.')->success()->send();
                    }),
                Action::make('notify_doctor')
                    ->label('Resend to Doctor')
                    ->icon('heroicon-o-bell-alert')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Radiology $record): bool => ! PatientPortal::isPatientUser()
                        && $record->status === 'completed'
                        && (ClinicalStaffAccess::canManageRadiology($record)
                            || (auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false)))
                    ->action(function (Radiology $record): void {
                        abort_unless(
                            ClinicalStaffAccess::canManageRadiology($record)
                                || (auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false),
                            403,
                        );

                        if (! ClinicalResultNotifier::notifyDoctor($record)) {
                            Notification::make()
                                ->title('The assigned doctor has no linked user account.')
                                ->body('Link a user account to the doctor before sending the result.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Assigned doctor notified again.')
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

    /** @return array<string, mixed> */
    private static function reportData(array $data): array
    {
        return [
            'result_summary' => $data['result_summary'] ?? null,
            'report_technique' => $data['report_technique'] ?? null,
            'comparison_studies' => $data['comparison_studies'] ?? null,
            'findings' => $data['findings'],
            'result_status' => $data['result_status'],
            'conclusion' => $data['conclusion'],
            'report_image' => $data['report_image'] ?? null,
            'critical_contact_name' => $data['critical_contact_name'] ?? null,
            'critical_contact_method' => $data['critical_contact_method'] ?? null,
            'critical_contacted_at' => $data['critical_contacted_at'] ?? null,
        ];
    }
}
