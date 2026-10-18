<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters\Schemas;

use App\Models\Inventory;
use App\Models\Patients;
use App\Services\ControlledSubstanceService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ControlledSubstanceRegisterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Controlled Substance Movement')
                    ->description('Record receipt, dispense, return, transfer or waste with a running balance (chain of custody).')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Select::make('inventory_item_id')
                            ->label('Controlled Medicine')
                            ->options(fn (): array => Inventory::query()->controlled()->orderBy('item_name')->pluck('item_name', 'item_id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set): void {
                                $item = $state ? Inventory::query()->where('item_id', $state)->first() : null;
                                if ($item) {
                                    $set('running_balance_hint', ControlledSubstanceService::currentBalance($item));
                                }
                            })
                            ->helperText('Only items flagged as controlled substances are listed.'),
                        Select::make('transaction_type')
                            ->label('Transaction')
                            ->options([
                                'receipt' => 'Receipt (in)',
                                'dispense' => 'Dispense (out)',
                                'return' => 'Patient return (in)',
                                'transfer_in' => 'Transfer in',
                                'transfer_out' => 'Transfer out',
                                'waste' => 'Waste / destruction',
                                'loss' => 'Loss / breakage',
                            ])
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        TextInput::make('running_balance_hint')
                            ->label('Current ledger balance')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—')
                            ->formatStateUsing(fn ($state) => $state === null ? '—' : (string) $state),
                        TextInput::make('batch_no')
                            ->label('Batch / Lot No.')
                            ->maxLength(80),
                        Select::make('patient_id')
                            ->label('Patient')
                            ->options(fn (): array => Patients::query()->orderBy('full_name')->pluck('full_name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->helperText('Required for dispense and patient returns.'),
                        TextInput::make('prescription_id')
                            ->label('Prescription ID')
                            ->maxLength(50),
                        TextInput::make('pharmacist_id')
                            ->label('Pharmacist')
                            ->default(fn () => Auth::id())
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('witness')
                            ->label('Witness')
                            ->maxLength(120)
                            ->helperText('A second staff member must witness dispensing/waste of controlled drugs.'),
                        DateTimePicker::make('occurred_at')
                            ->label('Occurred At')
                            ->default(now())
                            ->native(false),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(),
            ]);
    }
}
