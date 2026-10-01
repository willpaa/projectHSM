<?php

namespace App\Filament\Resources\Beds\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BedsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ward_id')
                    ->searchable(),
                TextColumn::make('ward_name')
                    ->searchable(),
                TextColumn::make('bed_id')
                    ->searchable(),
                TextColumn::make('bed_no')
                    ->searchable(),
                TextColumn::make('bed_type')
                    ->badge(),
                TextColumn::make('charge_per_day')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('occupancy_status')
                    ->badge(),
                TextColumn::make('current_patient_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('assigned_nurse')
                    ->searchable(),
                TextColumn::make('last_cleaned_at')
                    ->dateTime()
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
