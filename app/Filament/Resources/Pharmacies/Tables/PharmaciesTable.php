<?php

namespace App\Filament\Resources\Pharmacies\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Pharmacy;
use App\Services\InventoryService;
use App\Services\PharmacyDispensingService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PharmaciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('medicine_name')->label('Medicine')->searchable(),
                TextColumn::make('prescribed_medicine')->label('Prescribed as')->placeholder('-'),
                TextColumn::make('dosage_instructions')->label('Prescriber Instructions')->wrap()->placeholder('-'),
                TextColumn::make('patient.full_name')->label('Patient')->searchable()->placeholder('-'),
                TextColumn::make('stock_qty')->label('Qty to dispense')->numeric()->sortable(),
                TextColumn::make('unit_price')->label('Unit Price')->money('ETB'),
                TextColumn::make('queue_status')
                    ->label('Queue Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending', 'backordered' => 'warning',
                        'partially_dispensed' => 'info',
                        'dispensed' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('prescription_sale_id')->label('Prescription')->searchable()->placeholder('-'),
                TextColumn::make('created_at')->label('Sent to Pharmacy')->since()->sortable(),
            ])
            ->filters([
                TableFilters::distinct('medicine_name', Pharmacy::class, 'Medicine'),
                TableFilters::relationship('patient', 'full_name', 'Patient'),
                TableFilters::distinct('queue_status', Pharmacy::class, 'Queue Status'),
                TableFilters::distinct('prescription_sale_id', Pharmacy::class, 'Prescription'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('dispense')
                    ->label('Dispense')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Pharmacy $record): bool => $record->stock_qty > 0
                        && $record->queue_status !== 'dispensed'
                        && $record->inventoryItem !== null
                        && InventoryService::availableQuantity($record->inventoryItem) > 0)
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to Dispense')
                            ->numeric()
                            ->minValue(1)
                            ->default(fn (Pharmacy $record): int => $record->stock_qty)
                            ->helperText(fn (Pharmacy $record): string => 'Unexpired stock available: ' . ($record->inventoryItem
                                ? InventoryService::availableQuantity($record->inventoryItem)
                                : 0))
                            ->required(),
                        Select::make('payment_status')
                            ->label('Payment')
                            ->options(['paid' => 'Paid', 'pending' => 'Pending'])
                            ->default('paid')
                            ->required(),
                        TextInput::make('patient_id')
                            ->label('Patient ID or Code')
                            ->helperText('Leave empty to use the patient on the prescription.'),
                        Textarea::make('notes')->label('Dispensing Notes')->rows(2),
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        try {
                            $sale = PharmacyDispensingService::dispense(
                                $record,
                                (int) $data['quantity'],
                                (string) $data['payment_status'],
                                $data['patient_id'] ?? null,
                                $data['notes'] ?? null,
                                Auth::id(),
                            );

                            Notification::make()
                                ->title('Medicine dispensed successfully.')
                                ->body("Sale reference: {$sale->sale_id}")
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Dispensing failed.')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
