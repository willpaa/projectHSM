<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures\Schemas;

use App\Models\Inventory;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PharmacyStorageTemperatureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Storage Reading')
                    ->description('Log refrigerator / freezer temperature for cold-chain monitoring (WHO/FIP GPP).')
                    ->icon('heroicon-o-beaker')
                    ->columns(2)
                    ->schema([
                        TextInput::make('storage_location')
                            ->label('Storage Location')
                            ->placeholder('Fridge A, Freezer 1, Main store…')
                            ->required()
                            ->maxLength(120),
                        Select::make('inventory_item_id')
                            ->label('Medicine (optional)')
                            ->options(fn (): array => Inventory::query()->orderBy('item_name')->pluck('item_name', 'item_id')->all())
                            ->searchable()
                            ->preload()
                            ->helperText('Link the reading to a specific cold-chain item if applicable.'),
                        TextInput::make('temperature_celsius')
                            ->label('Temperature (°C)')
                            ->numeric()
                            ->step(0.01)
                            ->required(),
                        TextInput::make('humidity')
                            ->label('Humidity (%)')
                            ->numeric()
                            ->step(0.01)
                            ->minValue(0)
                            ->maxValue(100),
                        Toggle::make('within_range')
                            ->label('Within acceptable range')
                            ->default(true)
                            ->helperText('Turn off to flag an out-of-range excursion.'),
                        DateTimePicker::make('recorded_at')
                            ->label('Recorded At')
                            ->default(now())
                            ->native(false),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Corrective action taken for any excursion…'),
                    ])
                    ->collapsed(),
            ]);
    }
}
