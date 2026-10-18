<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Supplier;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier_code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contact_person')
                    ->label('Contact')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('email')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('purchase_orders_count')
                    ->label('POs')
                    ->counts('purchaseOrders')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Active')
                    ->placeholder('All suppliers')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->queries(
                        true: fn ($query) => $query->where('status', 'active'),
                        false: fn ($query) => $query->where('status', 'inactive'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }
}
