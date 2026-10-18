<?php

namespace App\Filament\Resources\Appointments\Schemas;

use App\Models\Doctors;
use App\Models\Patients;
use App\Support\ClinicalReportAccess;
use App\Support\PatientPortal;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppointmentsForm
{
    public static function configure(Schema $schema): Schema
    {
        $isPatient = PatientPortal::isPatientUser();
        $isDoctor = auth()->user()?->hasRole('doctor') ?? false;

        return $schema
            ->components([
                Section::make('Appointment details')
                    ->description('Choose the patient, doctor, visit type, and an available future time.')
                    ->schema([
                        Select::make('patient_id')
                            ->label('Patient')
                            ->relationship('patient', 'full_name')
                            ->getOptionLabelFromRecordUsing(fn (Patients $record): string => "{$record->full_name} ({$record->patient_id})")
                            ->searchable(['full_name', 'patient_id', 'mrn', 'phone'])
                            ->preload()
                            ->required()
                            ->visible(! $isPatient),
                        Hidden::make('patient_id')
                            ->default(PatientPortal::currentPatientId())
                            ->visible($isPatient)
                            ->dehydrated(),
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->relationship(
                                name: 'doctor',
                                titleAttribute: 'full_name',
                                modifyQueryUsing: fn ($query) => $query->where('status', 'active')->orderBy('full_name'),
                            )
                            ->getOptionLabelFromRecordUsing(fn (Doctors $record): string => collect([$record->full_name, $record->specialization])->filter()->implode(' — '))
                            ->searchable(['full_name', 'specialization', 'doctor_id'])
                            ->preload()
                            ->required()
                            ->visible(! $isDoctor),
                        Hidden::make('doctor_id')
                            ->default(ClinicalReportAccess::currentDoctorId())
                            ->visible($isDoctor)
                            ->dehydrated(),
                        DatePicker::make('date')
                            ->label('Appointment date')
                            ->native(false)
                            ->minDate(today())
                            ->required(),
                        TimePicker::make('time')
                            ->label('Start time')
                            ->seconds(false)
                            ->required(),
                        Select::make('duration_minutes')
                            ->label('Appointment length')
                            ->options([
                                15 => '15 minutes',
                                30 => '30 minutes',
                                45 => '45 minutes',
                                60 => '60 minutes',
                            ])
                            ->default(30)
                            ->required(),
                        Select::make('visit_type')
                            ->label('Visit type')
                            ->options([
                                'OPD' => 'Outpatient (OPD)',
                                'IPD' => 'Inpatient (IPD)',
                            ])
                            ->required(),
                        Textarea::make('reason')
                            ->label('Reason for visit')
                            ->maxLength(2000)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Staff notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Internal notes')
                            ->maxLength(2000)
                            ->visible(! $isPatient)
                            ->columnSpanFull(),
                    ])
                    ->visible(! $isPatient),
            ]);
    }
}
