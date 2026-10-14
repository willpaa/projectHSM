<?php

namespace App\Filament\Resources\Prescriptions\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Inventory;
use App\Models\Pharmacy;
use App\Models\Prescription;
use App\Models\User;
use App\Support\PatientPortal;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class PrescriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('arrival_flag')
                    ->label('Arrival')
                    ->state(fn (Prescription $record): string => $record->created_at?->isToday() ? 'New Today' : 'Old')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'New Today' ? 'success' : 'gray'),
                TextColumn::make('prescription_id')
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(),
                TextColumn::make('doctor.full_name')
                    ->label('Doctor')
                    ->searchable(),
                TextColumn::make('encounter_type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('prescribed_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TableFilters::relationship('patient', 'full_name', 'Patient'),
                TableFilters::relationship('doctor', 'full_name', 'Doctor'),
                TableFilters::select('encounter_type', [
                    'OPD' => 'OPD',
                    'IPD' => 'IPD',
                ]),
                TableFilters::select('status', [
                    'active' => 'Active',
                    'dispensed' => 'Dispensed',
                    'expired' => 'Expired',
                    'cancelled' => 'Cancelled',
                ]),
                TableFilters::dateRange('prescribed_date', 'Prescribed Date'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('send_to_pharmacy')
                    ->label('Send to Pharmacy')
                    ->icon('heroicon-o-building-storefront')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (Prescription $record): bool => ! PatientPortal::isPatientUser() && $record->status === 'active')
                    ->form([
                        Select::make('prescribed_medicine')
                            ->label('Prescribed Medicine')
                            ->options(fn (Prescription $record): array => collect($record->medicationNames())
                                ->mapWithKeys(fn (string $name): array => [$name => $name])
                                ->all())
                            ->searchable()
                            ->required()
                            ->helperText('Which medicine from this prescription are you dispensing?'),
                        Select::make('inventory_item_id')
                            ->label('Medicine From Inventory')
                            ->options(fn (): array => Inventory::query()->orderBy('item_name')
                                ->get()
                                ->mapWithKeys(fn (Inventory $item): array => [
                                    $item->item_id => sprintf('%s (%s in stock)', $item->item_name, InventoryService::availableQuantity($item)),
                                ])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Match the prescribed medicine to a stocked inventory item.'),
                        TextInput::make('quantity')
                            ->label('Quantity to Dispense')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required()
                            ->helperText('How many units to hand to the patient.'),
                        Textarea::make('pharmacy_note')
                            ->label('Note to Pharmacist')
                            ->rows(2)
                            ->placeholder('Optional note for the pharmacy team.')
                            ->maxLength(500),
                    ])
                    ->action(function (Prescription $record, array $data): void {
                        $alreadyQueued = Pharmacy::query()
                            ->where('prescription_sale_id', $record->prescription_id)
                            ->where('prescribed_medicine', $data['prescribed_medicine'] ?? '')
                            ->exists();

                        if ($alreadyQueued) {
                            Notification::make()
                                ->title('This prescribed medicine is already in Pharmacy queue.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $inventoryItem = Inventory::query()
                            ->where('item_id', $data['inventory_item_id'] ?? '')
                            ->first();

                        if (! $inventoryItem) {
                            Notification::make()
                                ->title('Selected medicine not found in Inventory.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $qtyToDispense = (int) ($data['quantity'] ?? 0);

                        if ($qtyToDispense < 1) {
                            Notification::make()
                                ->title('Quantity must be at least one.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $availableQuantity = InventoryService::availableQuantity($inventoryItem);

                        // Build the dosage instructions for the selected medicine from
                        // the prescription's normalised medication rows.
                        $selectedRow = collect($record->medicationRows())
                            ->firstWhere('drug', $data['prescribed_medicine'] ?? '');

                        $dosageInstructions = $selectedRow
                            ? trim(implode(' — ', array_filter([
                                $selectedRow['dosage'] ?? null,
                                $selectedRow['frequency'] ?? null,
                                $selectedRow['duration'] ?? null,
                                $selectedRow['instructions'] ?? null,
                            ])))
                            : null;

                        Pharmacy::create([
                            'medicine_id' => 0,
                            'inventory_item_id' => $inventoryItem->item_id,
                            'medicine_name' => $inventoryItem->item_name,
                            'batch_no' => null,
                            'stock_qty' => $qtyToDispense,
                            'queue_status' => $availableQuantity >= $qtyToDispense ? 'pending' : 'backordered',
                            'prescribed_medicine' => $data['prescribed_medicine'],
                            'dosage_instructions' => $dosageInstructions ?: ($data['pharmacy_note'] ?? null),
                            'unit_price' => $inventoryItem->selling_price,
                            'supplier_id' => $inventoryItem->supplier_id,
                            'reorder_level' => $inventoryItem->reorder_level,
                            'prescription_sale_id' => $record->prescription_id,
                            'issued_to_patient_id' => $record->patient_id,
                        ]);

                        $pharmacists = User::usersByRoleName('pharmacist');

                        if ($pharmacists->isNotEmpty()) {
                            Notification::make()
                                ->title('New Pharmacy Queue')
                                ->body("Prescription {$record->prescription_id} for {$record->patient?->full_name} has arrived.")
                                ->icon('heroicon-o-bell-alert')
                                ->sendToDatabase($pharmacists);
                        }

                        Notification::make()
                            ->title('Prescription sent to Pharmacy.')
                            ->success()
                            ->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
