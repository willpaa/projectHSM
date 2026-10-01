<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Pharmacies\PharmacyResource;
use App\Models\Pharmacy;
use App\Services\InventoryService;
use App\Services\PharmacyDispensingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RolePharmacyQueue extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pharmacy Queue')
            ->description('Prescriptions waiting for dispensing or stock.')
            ->query(fn (): Builder => Pharmacy::query()
                ->with(['patient', 'inventoryItem'])
                ->whereIn('queue_status', ['pending', 'backordered', 'partially_dispensed'])
                ->latest('created_at')
                ->limit(8))
            ->columns([
                TextColumn::make('prescribed_medicine')
                    ->label('Prescription')
                    ->placeholder('Direct pharmacy order')
                    ->description(fn (Pharmacy $record): ?string => $record->dosage_instructions),
                TextColumn::make('medicine_name')->label('Inventory medicine')->searchable(),
                TextColumn::make('patient.full_name')->label('Patient')->placeholder('Walk-in'),
                TextColumn::make('stock_qty')->label('Qty remaining')->numeric(),
                TextColumn::make('queue_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'backordered' => 'danger',
                        'partially_dispensed' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Received')->since(),
            ])
            ->recordActions([
                Action::make('dispense')
                    ->label('Dispense')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Pharmacy $record): bool => $record->stock_qty > 0 && $record->queue_status !== 'dispensed')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to dispense')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(fn (Pharmacy $record): int => (int) $record->stock_qty)
                            ->default(fn (Pharmacy $record): int => (int) $record->stock_qty)
                            ->helperText(fn (Pharmacy $record): string => $record->inventoryItem
                                ? 'Usable stock: ' . InventoryService::availableQuantity($record->inventoryItem)
                                : 'No inventory item is linked to this queue entry.')
                            ->required(),
                        Select::make('payment_status')
                            ->label('Payment Status')
                            ->options(['paid' => 'Paid', 'pending' => 'Pending'])
                            ->default('paid')
                            ->required(),
                        Textarea::make('notes')->label('Dispensing Notes')->rows(2),
                    ])
                    ->action(function (Pharmacy $record, array $data): void {
                        try {
                            $sale = PharmacyDispensingService::dispense(
                                $record,
                                (int) $data['quantity'],
                                (string) $data['payment_status'],
                                null,
                                $data['notes'] ?? null,
                                auth()->id(),
                            );

                            Notification::make()
                                ->title('Medicine dispensed')
                                ->body("Sale {$sale->sale_id} has been recorded.")
                                ->success()
                                ->send();
                        } catch (\RuntimeException $exception) {
                            Notification::make()
                                ->title('Dispensing failed')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordUrl(fn (Pharmacy $record): string => PharmacyResource::getUrl('index'))
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super_admin', 'pharmacy_staff', 'pharmacist', 'pharmacy', 'pharmcy']) ?? false;
    }
}
