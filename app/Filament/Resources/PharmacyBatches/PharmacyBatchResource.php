<?php

namespace App\Filament\Resources\PharmacyBatches;

use App\Filament\Resources\PharmacyBatches\Pages\ListPharmacyBatches;
use App\Filament\Resources\PharmacyBatches\Tables\PharmacyBatchesTable;
use App\Models\PharmacyBatch;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PharmacyBatchResource extends Resource
{
    protected static ?string $model = PharmacyBatch::class;

    protected static ?string $navigationParentItem = 'Pharmacy Management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Stock Lots';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return PharmacyBatchesTable::configure($table);
    }

    public static function canCreate(): bool { return false; }

    public static function canEdit($record): bool { return false; }

    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return ['index' => ListPharmacyBatches::route('/')];
    }
}
