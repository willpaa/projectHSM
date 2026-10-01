<?php

namespace App\Filament\Resources\Beds\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BedsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ward_id')
                    ->default(null),
                TextInput::make('ward_name')
                    ->required(),
                TextInput::make('bed_id')
                    ->required(),
                TextInput::make('bed_no')
                    ->required(),
                Select::make('bed_type')
                    ->options([
            'general' => 'General',
            'semi_private' => 'Semi private',
            'private' => 'Private',
            'icu' => 'Icu',
            'emergency' => 'Emergency',
        ])
                    ->default('general')
                    ->required(),
                TextInput::make('charge_per_day')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('occupancy_status')
                    ->options([
            'available' => 'Available',
            'occupied' => 'Occupied',
            'maintenance' => 'Maintenance',
            'reserved' => 'Reserved',
        ])
                    ->default('available')
                    ->required(),
                TextInput::make('current_patient_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('assigned_nurse')
                    ->default(null),
                DateTimePicker::make('last_cleaned_at'),
            ]);
    }
}
