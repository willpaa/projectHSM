<?php

namespace App\Filament\Resources\PharmacyStorageTemperatures;

use App\Filament\Resources\PharmacyStorageTemperatures\Pages\CreatePharmacyStorageTemperature;
use App\Filament\Resources\PharmacyStorageTemperatures\Pages\EditPharmacyStorageTemperature;
use App\Filament\Resources\PharmacyStorageTemperatures\Pages\ListPharmacyStorageTemperatures;
use App\Filament\Resources\PharmacyStorageTemperatures\Pages\ViewPharmacyStorageTemperature;
use App\Filament\Resources\PharmacyStorageTemperatures\Schemas\PharmacyStorageTemperatureForm;
use App\Filament\Resources\PharmacyStorageTemperatures\Schemas\PharmacyStorageTemperatureInfolist;
use App\Filament\Resources\PharmacyStorageTemperatures\Tables\PharmacyStorageTemperaturesTable;
use App\Models\PharmacyStorageTemperature;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PharmacyStorageTemperatureResource extends Resource
{
    protected static ?string $model = PharmacyStorageTemperature::class;


    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationLabel = 'Cold Chain Log';

    protected static ?string $recordTitleAttribute = 'storage_location';

    public static function form(Schema $schema): Schema
    {
        return PharmacyStorageTemperatureForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PharmacyStorageTemperatureInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PharmacyStorageTemperaturesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPharmacyStorageTemperatures::route('/'),
            'create' => CreatePharmacyStorageTemperature::route('/create'),
            'view' => ViewPharmacyStorageTemperature::route('/{record}'),
            'edit' => EditPharmacyStorageTemperature::route('/{record}/edit'),
        ];
    }
}
