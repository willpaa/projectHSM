<?php

namespace App\Filament\Resources\PharmacyBatchMovements;

use App\Filament\Resources\PharmacyBatchMovements\Pages\ListPharmacyBatchMovements;
use App\Filament\Resources\PharmacyBatchMovements\Tables\PharmacyBatchMovementsTable;
use App\Models\PharmacyBatchMovement;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PharmacyBatchMovementResource extends Resource
{
    protected static ?string $model = PharmacyBatchMovement::class;


    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';
 protected static ?string $navigationParentItem = 'Pharmacy Management';
    protected static ?string $navigationLabel = 'Stock Ledger';

    public static function form(Schema $schema): Schema { return $schema->components([]); }

    public static function table(Table $table): Table { return PharmacyBatchMovementsTable::configure($table); }

    public static function canCreate(): bool { return false; }

    public static function canEdit($record): bool { return false; }

    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return ['index' => ListPharmacyBatchMovements::route('/')];
    }
}
