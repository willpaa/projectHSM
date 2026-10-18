<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use App\Models\Supplier;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier Details')
                    ->icon('heroicon-o-building-storefront')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('supplier_code')->label('Supplier Code'),
                        TextEntry::make('name')->label('Supplier Name'),
                        TextEntry::make('contact_person')->label('Contact Person')->placeholder('-'),
                        TextEntry::make('phone')->placeholder('-'),
                        TextEntry::make('email')->placeholder('-'),
                        TextEntry::make('status')->badge()->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
                        TextEntry::make('address')->placeholder('-')->columnSpanFull(),
                        TextEntry::make('city')->placeholder('-'),
                        TextEntry::make('country')->placeholder('-'),
                        TextEntry::make('notes')->placeholder('-')->columnSpanFull(),
                    ]),
                Section::make('Procurement Summary')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('purchase_orders_count')
                            ->label('Purchase Orders')
                            ->state(fn (Supplier $record): int => $record->purchaseOrders()->count()),
                        TextEntry::make('created_at')->dateTime()->placeholder('-'),
                    ]),
            ]);
    }
}
