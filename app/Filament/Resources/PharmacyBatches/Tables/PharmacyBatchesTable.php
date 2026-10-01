<?php

namespace App\Filament\Resources\PharmacyBatches\Tables;

use App\Models\PharmacyBatch;
use App\Filament\Support\TableFilters;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PharmacyBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('inventoryItem.item_id')->label('Item ID')->searchable(),
                TextColumn::make('inventoryItem.item_name')->label('Medicine')->searchable(),
                TextColumn::make('batch_no')->label('Batch / Lot')->searchable(),
                TextColumn::make('quantity_received')->label('Received')->numeric(),
                TextColumn::make('quantity_remaining')->label('Available')->numeric()->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),
                TextColumn::make('expiry_date')->date()->sortable()
                    ->color(fn (?string $state): string => $state && $state < today()->toDateString() ? 'danger' : 'gray'),
                TextColumn::make('supplier_id')->label('Supplier')->placeholder('-'),
                TextColumn::make('reference')->label('PO / Reference')->searchable()->placeholder('-'),
                TextColumn::make('purchase_price')->money('ETB'),
                TextColumn::make('received_at')->dateTime()->sortable(),
            ])
            ->filters([
                TableFilters::distinct('supplier_id', PharmacyBatch::class, 'Supplier'),
                TableFilters::dateRange('expiry_date', 'Expiry Date'),
                TableFilters::createdAt(),
            ])
            ->defaultSort('expiry_date')
            ->paginated([10, 25, 50]);
    }
}
