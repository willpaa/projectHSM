<?php

namespace App\Filament\Resources\Radiologies\Schemas;

use App\Support\ClinicalReportAccess;
use App\Support\ClinicalStaffAccess;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                TextInput::make('modality')
                    ->maxLength(60),
                DatePicker::make('exam_date'),
                Textarea::make('result_summary')
                    ->columnSpanFull()
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isRadiologyStaff()),
                Select::make('result_status')
                    ->options([
                        'normal' => 'Normal',
                        'abnormal' => 'Abnormal',
                        'critical' => 'Critical',
                        'pending' => 'Pending',
                    ])
                    ->visible(fn (): bool => ClinicalReportAccess::isDoctorUser() || ClinicalStaffAccess::isRadiologyStaff()),
                Select::make('status')
                    ->options([
                        'ordered' => 'Ordered',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('ordered')
                    ->required(),
            ]);
    }
}
