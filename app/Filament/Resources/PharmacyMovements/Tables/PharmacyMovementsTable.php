<?php

namespace App\Filament\Resources\PharmacyMovements\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PharmacyMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pharmacy.medicine_name')->label('Medicine')->searchable(),
                TextColumn::make('movement_type')
                    ->badge(),
                TextColumn::make('direction')
                    ->badge(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('patient.full_name')->label('Patient')->searchable()->placeholder('-'),
                TextColumn::make('prescription_id')
                    ->searchable(),
                TextColumn::make('reference')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Recorded By')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
