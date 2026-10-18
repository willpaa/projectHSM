<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters\Tables;

use App\Filament\Support\TableFilters;
use App\Models\ControlledSubstanceRegister;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class ControlledSubstanceRegistersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('item.item_name')
                    ->label('Medicine')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('transaction_type')
                    ->label('Transaction')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ucfirst(str_replace('_', ' ', (string) $state)))
                    ->color(fn (?string $state): string => in_array($state, ['receipt', 'return', 'transfer_in'], true) ? 'success' : 'danger'),
                TextColumn::make('quantity')
                    ->formatStateUsing(fn (ControlledSubstanceRegister $record): string => in_array($record->transaction_type, ['receipt', 'return', 'transfer_in'], true)
                        ? '+' . $record->quantity
                        : '-' . $record->quantity),
                TextColumn::make('running_balance')
                    ->label('Balance')
                    ->badge()
                    ->color('info'),
                TextColumn::make('batch_no')
                    ->label('Batch / Lot')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('pharmacist.name')
                    ->label('Pharmacist')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('witness')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TableFilters::distinct('transaction_type', ControlledSubstanceRegister::class, 'Transaction'),
                TableFilters::distinct('inventory_item_id', ControlledSubstanceRegister::class, 'Medicine'),
                TableFilters::createdAt('occurred_at', 'Occurred Date'),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('occurred_at', 'desc');
    }
}
