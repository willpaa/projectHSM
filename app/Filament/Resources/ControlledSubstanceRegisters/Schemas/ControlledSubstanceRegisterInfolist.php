<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ControlledSubstanceRegisterInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Register Entry')
                    ->schema([
                        TextEntry::make('item.item_name')->label('Medicine'),
                        TextEntry::make('transaction_type')
                            ->label('Transaction')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => ucfirst(str_replace('_', ' ', (string) $state)))
                            ->color(fn (?string $state): string => in_array($state, ['receipt', 'return', 'transfer_in'], true) ? 'success' : 'danger'),
                        TextEntry::make('quantity'),
                        TextEntry::make('running_balance')
                            ->label('Running Balance')
                            ->badge(),
                        TextEntry::make('batch_no')->label('Batch / Lot')->placeholder('-'),
                        TextEntry::make('patient.full_name')->label('Patient')->placeholder('-'),
                        TextEntry::make('prescription_id')->label('Prescription')->placeholder('-'),
                        TextEntry::make('pharmacist.name')->label('Pharmacist')->placeholder('-'),
                        TextEntry::make('witness')->placeholder('-'),
                        TextEntry::make('occurred_at')->dateTime()->placeholder('-'),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
