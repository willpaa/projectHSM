<?php

namespace App\Filament\Resources\Inventories\Schemas;

use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
class InventoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item Identification')
                    ->description('How the medicine is identified and classified (WHO formulary / ATC).')
                    ->icon('heroicon-o-beaker')
                    ->columns(2)
                    ->schema([
                        TextInput::make('item_id')
                            ->label('Item Code')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->disabledOn('edit'),
                        TextInput::make('sku_code')
                            ->label('SKU')
                            ->helperText('Optional internal stock-keeping code.')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        TextInput::make('barcode')
                            ->label('Barcode (EAN/UPC)')
                            ->maxLength(50),
                        TextInput::make('item_name')
                            ->label('Brand / Trade Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('generic_name')
                            ->label('Generic Name')
                            ->maxLength(255),
                        TextInput::make('atc_code')
                            ->label('ATC Code')
                            ->helperText('WHO ATC classification, e.g. J01CA04.')
                            ->maxLength(20),
                        TextInput::make('drug_form')
                            ->label('Dosage Form')
                            ->placeholder('Tablet, capsule, syrup, injection…')
                            ->maxLength(60),
                        TextInput::make('strength')
                            ->label('Strength')
                            ->placeholder('500 mg, 10 mg/ml…')
                            ->maxLength(60),
                        TextInput::make('therapeutic_class')
                            ->label('Therapeutic Class')
                            ->maxLength(120),
                        Select::make('category')
                            ->label('Category')
                            ->options([
                                'antibiotic' => 'Antibiotic',
                                'analgesic' => 'Analgesic',
                                'antipyretic' => 'Antipyretic',
                                'antimalarial' => 'Antimalarial',
                                'cardiovascular' => 'Cardiovascular',
                                'gastrointestinal' => 'Gastrointestinal',
                                'respiratory' => 'Respiratory',
                                'dermatology' => 'Dermatology',
                                'ophthalmology' => 'Ophthalmology',
                                'supplies' => 'Medical Supplies',
                                'other' => 'Other',
                            ])
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Regulatory & Storage')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->schema([
                        Toggle::make('requires_prescription')
                            ->label('Prescription-only (Rx)')
                            ->helperText('Off = over-the-counter (OTC).')
                            ->default(true),
                        Toggle::make('is_controlled')
                            ->label('Controlled Substance')
                            ->helperText('Tracked in the controlled-drug register with chain of custody.'),
                        Toggle::make('is_active')
                            ->label('Active Item')
                            ->helperText('Inactive items are hidden from dispensing and sales.')
                            ->default(true),
                        Select::make('storage_condition')
                            ->label('Storage Condition')
                            ->options([
                                'ambient' => 'Ambient (room temperature)',
                                'cold' => 'Cold chain (2–8 °C)',
                                'frozen' => 'Frozen (−20 °C)',
                            ])
                            ->default('ambient')
                            ->required(),
                        TextInput::make('store_location')
                            ->label('Store Location')
                            ->placeholder('Shelf A2, Cold room 1…')
                            ->maxLength(120),
                    ]),
                Section::make('Stock, Pricing & Reorder')
                    ->icon('heroicon-o-cube')
                    ->columns(2)
                    ->schema([
                        TextInput::make('unit')
                            ->label('Base Unit')
                            ->placeholder('tab, vial, bottle…')
                            ->maxLength(30),
                        TextInput::make('pack_size')
                            ->label('Pack Size')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Units per pack, e.g. 30 tablets per pack.'),
                        TextInput::make('quantity')
                            ->label('Opening Stock Quantity')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->helperText('Live stock is adjusted with the Purchase / Adjust actions instead.'),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->required()
                            ->minDate(now())
                            ->helperText('Date when the inventory item expires.'),
                        TextInput::make('reorder_level')
                            ->label('Reorder Level')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(10)
                            ->helperText('Low-stock alert triggers at or below this quantity.'),
                        TextInput::make('purchase_price')
                            ->label('Purchase Price (per unit)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0.0)
                            ->prefix('ETB'),
                        TextInput::make('selling_price')
                            ->label('Selling Price (per unit)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->default(0.0)
                            ->prefix('ETB'),
                        TextInput::make('tax_rate')
                            ->label('Tax / VAT %')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->suffix('%'),
                        Select::make('supplier_ref_id')
                            ->label('Preferred Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Maintained under Pharmacy Management → Suppliers.'),
                    ]),
            ]);
    }
}

