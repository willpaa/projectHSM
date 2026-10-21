<?php

namespace App\Filament\Resources\Radiologies\Schemas;

use App\Support\ClinicalStaffAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RadiologyForm
{
    public static function configure(Schema $form): Schema
    {
        return $form
            ->components([
                TextInput::make('radiology_id')
                    ->required()
                    ->maxLength(50),
                Select::make('patient_id')
                    ->relationship('patient', 'full_name')
                    ->searchable()
                    ->required(),
                Select::make('doctor_id')
                    ->relationship('doctor', 'full_name')
                    ->searchable(),
                Select::make('radiologist_id')
                    ->label('Assigned Radiology Technician')
                    ->relationship(
                        'radiologist',
                        'full_name',
                        fn (Builder $query): Builder => $query
                            ->where('role', 'radiology_technician')
                            ->where('status', 'active')
                            ->whereNotNull('user_id')
                            ->orderBy('full_name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('exam_name')
                    ->maxLength(120),
                Select::make('modality')
                    ->options([
                        'X-Ray' => 'X-Ray',
                        'Ultrasound' => 'Ultrasound',
                        'CT' => 'CT',
                        'MRI' => 'MRI',
                        'Fluoroscopy' => 'Fluoroscopy',
                        'Mammography' => 'Mammography',
                        'Nuclear Medicine' => 'Nuclear Medicine',
                    ])
                    ->searchable(),
                TextInput::make('body_part')->label('Body Part / Region')->maxLength(120),
                Select::make('laterality')
                    ->label('Side')
                    ->options([
                        'not_applicable' => 'Not applicable',
                        'right' => 'Right',
                        'left' => 'Left',
                        'bilateral' => 'Bilateral',
                    ]),
                Select::make('urgency')
                    ->options(['routine' => 'Routine', 'urgent' => 'Urgent', 'emergency' => 'Emergency'])
                    ->default('routine'),
                Textarea::make('clinical_indication')->label('Clinical Indication / Reason')->rows(3),
                Textarea::make('clinical_history')->label('Relevant History / Examination Findings')->rows(3),
                Textarea::make('clinical_question')->label('Clinical Question to Be Answered')->rows(3),
                Select::make('pregnancy_status')
                    ->label('Pregnancy Screening (if applicable)')
                    ->options([
                        'not_applicable' => 'Not applicable',
                        'screened_not_pregnant' => 'Screened: not pregnant',
                        'possible_or_confirmed' => 'Possible or confirmed pregnancy',
                        'not_screened' => 'Not yet screened / unknown',
                    ])
                    ->visible(fn (Get $get): bool => in_array($get('modality'), ['X-Ray', 'CT', 'Fluoroscopy', 'Mammography'], true)),
                Select::make('contrast_plan')
                    ->label('Contrast Plan')
                    ->options([
                        'not_requested' => 'No contrast requested',
                        'requested' => 'Contrast requested; radiology to vet',
                        'radiologist_to_decide' => 'Radiologist to decide',
                    ]),
                Textarea::make('contrast_safety_notes')
                    ->label('Known Contrast Reaction / Kidney Function / Relevant Medication')
                    ->visible(fn (Get $get): bool => in_array($get('contrast_plan'), ['requested', 'radiologist_to_decide'], true))
                    ->columnSpanFull(),
                Textarea::make('mri_safety_notes')
                    ->label('MRI Implant / Device / Metal Safety Notes')
                    ->visible(fn (Get $get): bool => $get('modality') === 'MRI')
                    ->columnSpanFull(),
                DatePicker::make('exam_date'),
                Select::make('status')
                    ->options([
                        'ordered' => 'Ordered',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('ordered')
                    ->required()
                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false),
            ]);
    }
}
