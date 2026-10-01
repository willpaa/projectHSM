<?php

namespace App\Filament\Resources\PatientHistories\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PatientHistoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->relationship('patient', 'id')
                    ->required(),
                TextInput::make('encounter_id')
                    ->default(null),
                TextInput::make('source')
                    ->required()
                    ->default('pharmacy'),
                TextInput::make('activity')
                    ->required(),
                Textarea::make('details')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('occurred_at'),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
