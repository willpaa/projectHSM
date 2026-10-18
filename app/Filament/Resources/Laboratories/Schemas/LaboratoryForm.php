<?php

namespace App\Filament\Resources\Laboratories\Schemas;

use App\Support\ClinicalReportAccess;
use App\Support\ClinicalStaffAccess;
use App\Support\PatientPortal;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class LaboratoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('lab_id')
                    ->default(null),
                Select::make('patient_id')
                    ->relationship('patient', 'full_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (): bool => ! PatientPortal::isPatientUser()),
                Select::make('doctor_id')
                    ->relationship('doctor', 'full_name')
                    ->searchable()
                    ->preload()
                    ->default(fn (): ?int => ClinicalReportAccess::currentDoctorId())
                    ->disabled(fn (): bool => ClinicalReportAccess::isDoctorUser())
                    ->helperText(fn (): ?string => ClinicalReportAccess::isDoctorUser()
                        ? 'Requests are recorded under your own doctor account.'
                        : null)
                    ->visible(fn (): bool => ! PatientPortal::isPatientUser()),
                Select::make('laboratorist_id')
                    ->label('Laboratorist (Lab Technician)')
                    ->relationship(
                        'laboratorist',
                        'full_name',
                        fn (Builder $query): Builder => $query
                            ->where('role', 'lab_technician')
                            ->where('status', 'active')
                            ->whereNotNull('user_id')
                            ->orderBy('full_name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->visible(fn (): bool => ! PatientPortal::isPatientUser()),
                TextInput::make('test_name')
                    ->default(null),
                TextInput::make('test_type')
                    ->default(null),
                TextInput::make('sample_type')
                    ->default(null),
                DatePicker::make('test_date'),
                DatePicker::make('result_date')
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser()),
                Textarea::make('result_value')
                    ->default(null)
                    ->columnSpanFull()
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isLabStaff()),
                Select::make('result_status')
                    ->options([
                        'normal' => 'Normal',
                        'abnormal' => 'Abnormal',
                        'critical' => 'Critical',
                        'pending' => 'Pending',
                    ])
                    ->default(null)
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isLabStaff()),
                TextInput::make('normal_range')
                    ->default(null)
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isLabStaff()),
                TextInput::make('cost')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull()
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isLabStaff()),
                Select::make('status')
                    ->options([
                        'ordered' => 'Ordered',
                        'sample_collected' => 'Sample collected',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('ordered')
                    ->required(),
            ]);
    }
}

