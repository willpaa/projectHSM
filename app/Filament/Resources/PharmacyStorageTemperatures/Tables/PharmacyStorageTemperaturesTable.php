<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures\Tables;

use App\Filament\Support\TableFilters;
use App\Models\PharmacyStorageTemperature;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PharmacyStorageTemperaturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Recorded')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('storage_location')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.item_name')
                    ->label('Medicine')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('temperature_celsius')
                    ->label('Temp (°C)')
                    ->suffix(' °C')
                    ->sortable()
                    ->color(fn (PharmacyStorageTemperature $record): string => $record->within_range ? 'success' : 'danger'),
                TextColumn::make('humidity')
                    ->label('Humidity (%)')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('within_range')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'In range' : 'Out of range')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                TextColumn::make('recorder.name')
                    ->label('Recorded By')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('within_range')
                    ->label('In Range')
                    ->placeholder('All readings')
                    ->trueLabel('In range only')
                    ->falseLabel('Excursions only'),
                TableFilters::distinct('storage_location', PharmacyStorageTemperature::class, 'Location'),
                TableFilters::createdAt('recorded_at', 'Recorded Date'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('recorded_at', 'desc');
    }
}
