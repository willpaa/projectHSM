<?php

namespace App\Filament\Resources\Triages\Schemas;

use App\Support\ClinicalNurseAccess;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TriageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('triage_id'),
                Select::make('patient_id')
                    ->relationship('patient', 'full_name')
                    ->searchable()
                    ->required(),
                Select::make('nurse_id')
                    ->label('Assigned Triage Nurse')
                    ->relationship(
                        'nurse',
                        'full_name',
                        fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query
                            ->where('role', 'triage_nurse')
                            ->where('status', 'active')
                            ->when(
                                ClinicalNurseAccess::isTriageNurse()
                                    && ! auth()->user()?->hasAnyRole(['admin', 'super_admin']),
                                fn ($nurseQuery) => $nurseQuery->where('user_id', auth()->id()),
                            )
                            ->orderBy('full_name'),
                    )
                    ->searchable()
                    ->default(fn (): ?int => ClinicalNurseAccess::currentNurseId('triage_nurse'))
                    ->disabled(fn (): bool => ClinicalNurseAccess::isTriageNurse()
                        && ! auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                    ->required(fn (): bool => ClinicalNurseAccess::isTriageNurse()
                        && ! auth()->user()?->hasAnyRole(['admin', 'super_admin']))
                    ->dehydrated(),
                TextInput::make('encounter_id')
                    ->maxLength(50),
                Select::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ])
                    ->default('medium')
                    ->required(),
                TextInput::make('chief_complaint')
                    ->maxLength(255),
                TextInput::make('temperature')
                    ->maxLength(20),
                TextInput::make('blood_pressure')
                    ->maxLength(20),
                TextInput::make('pulse_rate')
                    ->numeric(),
                TextInput::make('respiratory_rate')
                    ->numeric(),
                TextInput::make('oxygen_saturation')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'waiting' => 'Waiting',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                       
                    ])
                    ->default('waiting')
                    ->required()
                    ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false),
            ]);
    }
}
