<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\Inventory;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Purchase Order')
                    ->description('Who you are ordering from and when it is expected.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(2)
                    ->schema([
                        TextInput::make('po_number')
                            ->label('PO Number')
                            ->default(fn (): string => 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5)))
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->dehydrated(),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->options(fn (): array => Supplier::query()->where('status', 'active')->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('order_date')
                            ->label('Order Date')
                            ->default(now())
                            ->required()
                            ->native(false),
                        DatePicker::make('expected_date')
                            ->label('Expected Date')
                            ->native(false),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'ordered' => 'Ordered',
                                'partial' => 'Partially Received',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('ordered')
                            ->required(),
                    ]),

                Section::make('Order Items')
                    ->description('Add each medicine you are ordering with the quantity and unit cost.')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->hiddenLabel()
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add Item')
                            ->reorderable(false)
                            ->schema([
                                Select::make('inventory_item_id')
                                    ->label('Medicine')
                                    ->options(fn (): array => Inventory::query()->orderBy('item_name')->pluck('item_name', 'item_id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set): void {
                                        $item = $state ? Inventory::query()->where('item_id', $state)->first() : null;
                                        if ($item) {
                                            $set('unit_cost', (float) $item->purchase_price);
                                        }
                                    })
                                    ->columnSpan(2),
                                TextInput::make('quantity_ordered')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $get, callable $set) => self::recalcLine($set, $get))
                                    ->columnSpan(1),
                                TextInput::make('unit_cost')
                                    ->label('Unit Cost')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('ETB')
                                    ->reactive()
                                    ->afterStateUpdated(fn (callable $get, callable $set) => self::recalcLine($set, $get))
                                    ->columnSpan(1),
                            ])
                            ->columnSpanFull(),
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

    /**
     * Recalculate a repeater line's total from quantity × unit cost.
     */
    private static function recalcLine(callable $set, callable $get): void
    {
        $quantity = (float) ($get('quantity_ordered') ?? 0);
        $unitCost = (float) ($get('unit_cost') ?? 0);

        $set('line_total', $quantity * $unitCost);
    }
}

