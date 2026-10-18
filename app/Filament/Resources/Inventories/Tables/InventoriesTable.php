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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
                // ── Item Identification (matches Form Section 1) ──
                TextColumn::make('item_id')
                    ->label('Item Code')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('sku_code')
                    ->label('SKU')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('barcode')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('item_name')
                    ->label('Brand / Trade Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('generic_name')
                    ->label('Generic Name')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('atc_code')
                    ->label('ATC Code')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('drug_form')
                    ->label('Dosage Form')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('strength')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('therapeutic_class')
                    ->label('Therapeutic Class')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? ucwords(str_replace('_', ' ', (string) $state))
                        : 'Uncategorised')
                    ->color('info')
                    ->toggleable(),
                // ── Regulatory & Storage (matches Form Section 2) ──
                TextColumn::make('requires_prescription')
                    ->label('Rx / OTC')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Rx' : 'OTC')
                    ->color(fn (bool $state): string => $state ? 'warning' : 'success')
                    ->toggleable(),
                TextColumn::make('is_controlled')
                    ->label('Controlled')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Controlled' : '-')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'gray')
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Archived')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->toggleable(),
                TextColumn::make('storage_condition')
                    ->label('Storage Condition')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => ucfirst((string) $state))
                    ->color(fn (?string $state): string => in_array($state, ['cold', 'frozen'], true) ? 'info' : 'gray')
                    ->toggleable(),
                TextColumn::make('store_location')
                    ->label('Store Location')
                    ->placeholder('-')
                    ->toggleable(),
                // ── Stock, Pricing & Reorder (matches Form Section 3) ──
                TextColumn::make('unit')
                    ->label('Base Unit')
                    ->toggleable(),
                TextColumn::make('pack_size')
                    ->label('Pack Size')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->label('Opening Stock Qty')
                    ->numeric()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Quantity')
                            ->query(fn ($query) => $query->selectRaw('SUM(quantity) as aggregate'))
                    ),
                TextColumn::make('reorder_level')
                    ->label('Reorder Level')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('purchase_price')
                    ->label('Purchase Price')
                    ->money('ETB')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('selling_price')
                    ->label('Selling Price')
                    ->money('ETB')
                    ->sortable(),
                TextColumn::make('tax_rate')
                    ->label('Tax / VAT %')
                    ->formatStateUsing(fn ($state): string => $state > 0 ? $state . '%' : '-')
                    ->toggleable(),
                TextColumn::make('supplier.name')
                    ->label('Preferred Supplier')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->filters([
                TableFilters::distinct('category', Inventory::class, 'Category'),
                TableFilters::distinct('unit', Inventory::class, 'Unit'),
                TableFilters::dateRange('expiry_date', 'Expiry Date'),
                SelectFilter::make('stock_status')
                    ->label('Stock Status')
                    ->options([
                        'in_stock' => 'In stock',
                        'low_stock' => 'Low stock (at/below reorder level)',
                        'out_of_stock' => 'Out of stock',
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $status = $data['value'] ?? null;
                        if (! $status) {
                            return;
                        }
                        $query->when($status === 'out_of_stock', fn ($q) => $q->where('quantity', '<=', 0))
                            ->when($status === 'low_stock', fn ($q) => $q
                                ->where('quantity', '>', 0)
                                ->whereColumn('quantity', '<=', 'reorder_level'))
                            ->when($status === 'in_stock', fn ($q) => $q->whereColumn('quantity', '>', 'reorder_level'));
                    }),
                SelectFilter::make('expiry_status')
                    ->label('Expiry Status')
                    ->options([
                        'expired' => 'Expired',
                        'expiring' => 'Expiring within 90 days',
                        'valid' => 'Valid (>90 days)',
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $status = $data['value'] ?? null;
                        if (! $status) {
                            return;
                        }
                        $query->when($status === 'expired', fn ($q) => $q
                            ->whereNotNull('expiry_date')->where('expiry_date', '<', today()))
                            ->when($status === 'expiring', fn ($q) => $q
                                ->whereNotNull('expiry_date')->whereBetween('expiry_date', [today(), today()->addDays(90)]))
                            ->when($status === 'valid', fn ($q) => $q
                                ->whereNull('expiry_date')->orWhere('expiry_date', '>', today()->addDays(90)));
                    }),
                TernaryFilter::make('is_active')
                    ->label('Active Items')
                    ->placeholder('All items')
                    ->trueLabel('Active only')
                    ->falseLabel('Archived only'),
                TableFilters::distinct('storage_condition', Inventory::class, 'Storage Condition'),
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
                            ->label('Supplier Code (legacy)')
                            ->default(fn (Inventory $record): ?string => $record->supplier_id)
                            ->helperText('Or choose a registered supplier below — it is stored against the batch record.'),
                        Select::make('supplier_ref_id')
                            ->label('Registered Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (Inventory $record): ?int => $record->supplier_ref_id),
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

                            if (filled($data['supplier_ref_id'] ?? null)) {
                                $record->update(['supplier_ref_id' => (int) $data['supplier_ref_id']]);
                            }

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
