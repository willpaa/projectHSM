<?php

namespace App\Filament\Resources\Prescriptions\Schemas;

use App\Models\Doctors;
use App\Models\Patients;
use App\Support\ClinicalReportAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PrescriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Patient & Encounter')
                    ->description('Who this prescription is written for and the visit it belongs to.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        Select::make('patient_id')
                            ->label('Patient')
                            ->options(fn (): array => Patients::query()->orderBy('full_name')->pluck('full_name', 'id')->all())
                            ->getOptionLabelUsing(fn (string $value): ?string => Patients::query()->find($value)?->full_name)
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('doctor_id')
                            ->label('Prescribing Doctor')
                            ->options(fn (): array => ClinicalReportAccess::isDoctorUser()
                                ? Doctors::query()->whereKey(ClinicalReportAccess::currentDoctorId())->pluck('full_name', 'id')->all()
                                : Doctors::query()->orderBy('full_name')->pluck('full_name', 'id')->all())
                            ->searchable()
                            ->required()
                            ->default(fn (): ?int => ClinicalReportAccess::currentDoctorId())
                            ->disabled(fn (): bool => ClinicalReportAccess::isDoctorUser())
                            ->dehydrated()
                            ->helperText(fn (): bool => ClinicalReportAccess::isDoctorUser()
                                ? 'Locked to your doctor account — prescriptions are written by you.'
                                : null),
                        Select::make('encounter_type')
                            ->label('Encounter Type')
                            ->options([
                                'OPD' => 'OPD (Outpatient)',
                                'IPD' => 'IPD (Inpatient)',
                            ])
                            ->placeholder('Select encounter type')
                            ->dehydrated(),
                        TextInput::make('encounter_id')
                            ->label('Encounter ID')
                            ->maxLength(50)
                            ->placeholder('e.g. OPD-20261014-XXXXXX')
                            ->dehydrated(),
                        DatePicker::make('prescribed_date')
                            ->label('Prescription Date')
                            ->default(now())
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Diagnosis')
                    ->description('Clinical findings that justify this prescription.')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        Textarea::make('diagnosis')
                            ->label('Diagnosis / Chief Complaint')
                            ->rows(3)
                            ->placeholder('e.g. Acute upper respiratory tract infection')
                            ->columnSpanFull(),
                    ]),
                Section::make('Prescribed Medications (Rx)')
                    ->description('Add each medicine on its own row — drug, dose, frequency, duration, quantity and instructions.')
                    ->icon('heroicon-o-beaker')
                    ->schema([
                        Repeater::make('medications')
                            ->hiddenLabel()
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Add Medicine')
                            ->reorderable(false)
                            ->cloneable()
                            ->table([
                                TableColumn::make('Drug / Medicine')->markAsRequired(),
                                TableColumn::make('Dosage'),
                                TableColumn::make('Frequency'),
                                TableColumn::make('Duration'),
                                TableColumn::make('Quantity'),
                                TableColumn::make('Instructions'),
                            ])
                            ->schema([
                                TextInput::make('drug')
                                    ->label('Drug / Medicine')
                                    ->placeholder('e.g. Amoxicillin')
                                    ->required()
                                    ->maxLength(120)
                                    ->datalist(fn (): array => self::knownMedicines()),
                                TextInput::make('dosage')
                                    ->label('Dosage')
                                    ->placeholder('e.g. 500 mg')
                                    ->maxLength(60),
                                TextInput::make('frequency')
                                    ->label('Frequency')
                                    ->placeholder('e.g. 3 times daily (TID)')
                                    ->maxLength(80),
                                TextInput::make('duration')
                                    ->label('Duration')
                                    ->placeholder('e.g. 7 days')
                                    ->maxLength(60),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->placeholder('e.g. 21 tabs')
                                    ->maxLength(60),
                                TextInput::make('instructions')
                                    ->label('Instructions')
                                    ->placeholder('e.g. After meals, with water')
                                    ->maxLength(160),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('General Instructions & Notes')
                    ->description('Overall advice for the patient and any internal notes.')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        Textarea::make('instructions')
                            ->label('General Instructions')
                            ->rows(3)
                            ->placeholder('e.g. Rest, drink plenty of fluids, return if fever persists beyond 48 hours.')
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->placeholder('Any additional clinical notes (optional).')
                            ->columnSpanFull(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'dispensed' => 'Dispensed',
                                'expired' => 'Expired',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }

    /**
     * Suggestions drawn from the pharmacy inventory so doctors can pick
     * medicines that are actually stocked.
     *
     * @return array<int, string>
     */
    private static function knownMedicines(): array
    {
        return \App\Models\Inventory::query()
            ->orderBy('item_name')
            ->limit(200)
            ->pluck('item_name')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}


