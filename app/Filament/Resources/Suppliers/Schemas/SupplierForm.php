<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Supplier Information')
                    ->icon('heroicon-o-building-storefront')
                    ->columns(2)
                    ->schema([
                        TextInput::make('supplier_code')
                            ->label('Supplier Code')
                            ->disabled()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->dehydrated()
                            ->placeholder('Generated automatically')
                            ->helperText('Auto-generated, e.g. SUP-001.'),
                        TextInput::make('tin_number')
                            ->label('TIN Number')
                            ->maxLength(50)
                            ->helperText('Tax identification number of the supplier.'),
                        TextInput::make('license_number')
                            ->label('Drug License Number')
                            ->maxLength(80)
                            ->helperText('Regulatory trading license of the pharmaceutical supplier.'),
                        DatePicker::make('license_expires_at')
                            ->label('License Expiry')
                            ->minDate(now())
                            ->helperText('Suppliers with expired licenses should be deactivated.'),
                        TextInput::make('name')
                            ->label('Supplier Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(40),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                        TextInput::make('address')
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('city')
                            ->maxLength(120),
                        TextInput::make('country')
                            ->maxLength(120),
                        Select::make('payment_terms')
                            ->label('Payment Terms')
                            ->options([
                                'cod' => 'Cash on Delivery',
                                'net_15' => 'Net 15 days',
                                'net_30' => 'Net 30 days',
                                'net_60' => 'Net 60 days',
                                'prepaid' => 'Prepaid',
                            ])
                            ->default('cod')
                            ->required(),
                        TextInput::make('credit_limit')
                            ->label('Credit Limit (ETB)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('ETB'),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
