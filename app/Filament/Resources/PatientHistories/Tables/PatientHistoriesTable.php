<?php

namespace App\Filament\Resources\PatientHistories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PatientHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(),
                TextColumn::make('encounter_id')
                    ->label('Encounter')
                    ->searchable(),
                TextColumn::make('source')
                    ->badge()
                    ->searchable(),
                TextColumn::make('activity')
                    ->searchable(),
                TextColumn::make('details')
                    ->label('Details')
                    ->wrap()
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('occurred_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('occurred_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
