<?php

namespace App\Filament\Resources\PharmacyMovements;

use App\Filament\Resources\PharmacyMovements\Pages\ListPharmacyMovements;
use App\Filament\Resources\PharmacyMovements\Tables\PharmacyMovementsTable;
use App\Models\PharmacyMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PharmacyMovementResource extends Resource
{
    protected static ?string $model = PharmacyMovement::class;
    protected static ?string $navigationLabel = 'Dispensing Movements';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
  protected static ?string $navigationParentItem = 'Pharmacy Management';
    public static function table(Table $table): Table
    {
        return PharmacyMovementsTable::configure($table);
    }

    public static function canCreate(): bool { return false; }

    public static function canEdit($record): bool { return false; }

    public static function canDelete($record): bool { return false; }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPharmacyMovements::route('/'),
        ];
    }
}
