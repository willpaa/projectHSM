<?php

namespace App\Filament\Resources\Beds;

use App\Filament\Resources\Beds\Pages\CreateBeds;
use App\Filament\Resources\Beds\Pages\EditBeds;
use App\Filament\Resources\Beds\Pages\ListBeds;
use App\Filament\Resources\Beds\Schemas\BedsForm;
use App\Filament\Resources\Beds\Tables\BedsTable;
use App\Models\Beds;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BedsResource extends Resource
{
    protected static ?string $model = Beds::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BedsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BedsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeds::route('/'),
            'create' => CreateBeds::route('/create'),
            'edit' => EditBeds::route('/{record}/edit'),
        ];
    }
}
