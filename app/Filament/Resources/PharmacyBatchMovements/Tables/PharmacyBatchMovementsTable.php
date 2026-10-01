<?php

namespace App\Filament\Resources\PharmacyBatchMovements\Tables;

use App\Models\PharmacyBatchMovement;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PharmacyBatchMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('batch.inventoryItem.item_name')->label('Medicine')->searchable(),
                TextColumn::make('batch.batch_no')->label('Batch / Lot')->searchable(),
                TextColumn::make('operation_type')->label('Activity')->badge(),
                TextColumn::make('quantity_change')->label('Quantity Change')->numeric()->sortable()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('reference')->label('Reference')->searchable()->placeholder('-'),
                TextColumn::make('notes')->limit(50)->placeholder('-'),
                TextColumn::make('user.name')->label('Recorded By')->placeholder('System'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100]);
    }
}
