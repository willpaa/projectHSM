<?php

namespace App\Filament\Resources\PharmacyMovements\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PharmacyMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pharmacy_id')
                    ->relationship('pharmacy', 'id')
                    ->required(),
                Select::make('movement_type')
                    ->options([
            'dispense' => 'Dispense',
            'receive' => 'Receive',
            'loss_adjustment' => 'Loss adjustment',
            'transfer' => 'Transfer',
            'disposal' => 'Disposal',
        ])
                    ->required(),
                Select::make('direction')
                    ->options(['in' => 'In', 'out' => 'Out', 'adjust' => 'Adjust'])
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Select::make('patient_id')
                    ->relationship('patient', 'id')
                    ->default(null),
                TextInput::make('prescription_id')
                    ->default(null),
                TextInput::make('reference')
                    ->default(null),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
