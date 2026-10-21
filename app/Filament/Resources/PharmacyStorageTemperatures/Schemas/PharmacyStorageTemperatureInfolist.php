<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PharmacyStorageTemperatureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Reading')
                    ->schema([
                        TextEntry::make('storage_location')->label('Storage Location'),
                        TextEntry::make('item.item_name')->label('Medicine')->placeholder('-'),
                        TextEntry::make('temperature_celsius')->label('Temperature (°C)')->suffix(' °C'),
                        TextEntry::make('humidity')->label('Humidity (%)')->placeholder('-')->suffix(' %'),
                        TextEntry::make('within_range')
                            ->label('In Range')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Within range' : 'Out of range')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('recorded_at')->dateTime()->placeholder('-'),
                        TextEntry::make('recorder.name')->label('Recorded By')->placeholder('-'),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
