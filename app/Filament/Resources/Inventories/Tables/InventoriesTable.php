<?php

namespace App\Filament\Resources\Inventories\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Inventory;
use App\Models\Patients;
use App\Services\InventoryService;
use App\Services\PharmacyDirectSaleService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Throwable;

class InventoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('item_id')
                    ->searchable(),
                TextColumn::make('item_name')
                    ->searchable(),
                TextColumn::make('category')
                    ->searchable(),
                TextColumn::make('unit')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state, Inventory $record): string => $state <= $record->reorder_level ? 'danger' : 'success')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Quantity')
                            ->query(fn ($query) => $query->selectRaw('SUM(quantity) as aggregate'))
                    ),
                TextColumn::make('reorder_level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('supplier_id')
                    ->searchable(),
                TextColumn::make('purchase_price')
                    ->money('ETB')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Total Purchase (quantity × purchase)')
                    ->getStateUsing(function ($record) {
                        return $record->purchase_price * $record->quantity;
                    })
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Purchase')
                            ->using(fn ($query) => $query->sum(\DB::raw('purchase_price * quantity')))
                            ->money('ETB')
                    )
                    ->money('ETB'),
                TextColumn::make('selling_price')
                    ->money('ETB')
                    ->sortable(),
                TextColumn::make('total_celling')
                    ->label('Total Selling Price')
                    ->getStateUsing(function ($record) {
                        return $record->selling_price * $record->quantity;
                    })
                    ->summarize(
                        Summarizer::make()
                            ->label('Total Selling Price')
                            ->using(fn ($query) => $query->sum(\DB::raw('selling_price * quantity')))
                            ->money('ETB')
                    )
                    ->money('ETB'),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('store_location')
                    ->searchable(),
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
                TableFilters::distinct('category', Inventory::class, 'Category'),
                TableFilters::distinct('unit', Inventory::class, 'Unit'),
                TableFilters::distinct('supplier_id', Inventory::class, 'Supplier'),
                TableFilters::distinct('store_location', Inventory::class, 'Store Location'),
                TableFilters::dateRange('expiry_date', 'Expiry Date'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('purchase_order')
                    ->label('Purchase Order')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('qty')
                            ->label('Quantity Received')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('batch_no')
                            ->label('Batch / Lot Number')
                            ->required()
                            ->maxLength(80),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->native(false)
                            ->minDate(today()),
                        TextInput::make('purchase_price')
                            ->label('Unit Purchase Cost')
                            ->numeric()
                            ->minValue(0)
                            ->default(fn (Inventory $record): float => (float) $record->purchase_price)
                            ->required(),
                        TextInput::make('supplier_id')
                            ->label('Supplier')
                            ->default(fn (Inventory $record): ?string => $record->supplier_id),
                        TextInput::make('po_id')
                            ->label('PO / Invoice Reference')
                            ->required(),
                        Textarea::make('note')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        try {
                            InventoryService::purchaseOrder(
                                $record,
                                (int) $data['qty'],
                                (string) $data['po_id'],
                                $data['note'] ?? null,
                                (string) $data['batch_no'],
                                $data['expiry_date'] ?? null,
                                (float) $data['purchase_price'],
                                $data['supplier_id'] ?? null,
                            );

                            Notification::make()
                                ->title('Purchase stock added successfully.')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Purchase order failed.')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('sales_invoice')
                    ->label('Sales Invoice')
                    ->icon('heroicon-o-shopping-cart')
                    ->color('info')
                    ->form([
                        TextInput::make('qty')
                            ->label('Quantity Sold')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('invoice_id')
                            ->label('Invoice Reference')
                            ->required(),
                        TextInput::make('patient_id')
                            ->label('Patient ID or Code')
                            ->helperText('Optional for direct OTC sale. Enter patients.id or patient code.'),
                        Textarea::make('note')
                            ->label('Notes')
                            ->rows(2),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        try {
                            $resolvedPatientId = null;

                            if (filled($data['patient_id'] ?? null)) {
                                $resolvedPatientId = Patients::query()
                                    ->whereKey($data['patient_id'])
                                    ->value('id')
                                    ?? Patients::query()
                                        ->where('patient_id', (string) $data['patient_id'])
                                        ->value('id');

                                if (! $resolvedPatientId) {
                                    Notification::make()
                                        ->title('Patient not found.')
                                        ->danger()
                                        ->send();

                                    return;
                                }
                            }

                            PharmacyDirectSaleService::sell(
                                $record,
                                (int) $data['qty'],
                                (string) $data['invoice_id'],
                                $resolvedPatientId,
                                $data['note'] ?? null,
                                auth()->id(),
                            );

                            Notification::make()
                                ->title('Sales invoice posted and pharmacy sale recorded.')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Sales invoice failed.')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('remove_expired')
                    ->label('Expired')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        TextInput::make('qty')
                            ->label('Expired Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        Textarea::make('note')
                            ->label('Disposal Note')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        try {
                            InventoryService::removeExpired(
                                $record,
                                (int) $data['qty'],
                                $data['note'] ?? null,
                            );

                            Notification::make()
                                ->title('Expired stock removed.')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Expiry removal failed.')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('adjust_stock')
                    ->label('Adjust')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('qty')
                            ->label('Adjustment Quantity')
                            ->numeric()
                            ->required()
                            ->helperText('Use positive numbers to add stock and negative numbers to subtract.'),
                        TextInput::make('reference')
                            ->label('Adjustment Reference')
                            ->default('ADJ-' . now()->format('YmdHis'))
                            ->required(),
                        Textarea::make('note')
                            ->label('Reason')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (Inventory $record, array $data): void {
                        try {
                            InventoryService::adjustStock(
                                $record,
                                (int) $data['qty'],
                                $data['note'] ?? null,
                                (string) $data['reference'],
                            );

                            Notification::make()
                                ->title('Inventory adjustment saved.')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Inventory adjustment failed.')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
