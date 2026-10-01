<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use App\Models\Doctors;
use App\Models\Patients;
use App\Support\ClinicalReportAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PrescriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('prescription_id')
                    ->required()
                    ->maxLength(50),
                Select::make('patient_id')
                    ->options(fn (): array => Patients::query()->orderBy('full_name')->pluck('full_name', 'id')->all())
                    ->searchable()
                    ->required(),
                Select::make('doctor_id')
                    ->options(fn (): array => ClinicalReportAccess::isDoctorUser()
                        ? Doctors::query()->whereKey(ClinicalReportAccess::currentDoctorId())->pluck('full_name', 'id')->all()
                        : Doctors::query()->orderBy('full_name')->pluck('full_name', 'id')->all())
                    ->searchable()
                    ->required()
                    ->default(fn (): ?int => ClinicalReportAccess::currentDoctorId())
                    ->disabled(fn (): bool => ClinicalReportAccess::isDoctorUser())
                    ->dehydrated(),
                Select::make('encounter_type')
                    ->options([
                        'OPD' => 'OPD',
                        'IPD' => 'IPD',
                    ])
                    ->placeholder('Select encounter type')
                    ->dehydrated(),
                TextInput::make('encounter_id')
                    ->maxLength(50)
                    ->dehydrated(),
                DatePicker::make('prescribed_date')
                    ->required(),
                Textarea::make('diagnosis')
                    ->rows(3)
                    ->columnSpanFull(),
                KeyValue::make('medications')
                    ->keyLabel('Medicine')
                    ->valueLabel('Dose / Frequency / Quantity')
                    ->helperText('Add each medicine separately. Include dose, route, frequency, duration, and quantity in its instructions.')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('instructions')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'dispensed' => 'Dispensed',
                        'expired' => 'Expired',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}
