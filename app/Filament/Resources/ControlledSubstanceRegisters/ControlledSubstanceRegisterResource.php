<?php

namespace App\Filament\Resources\ControlledSubstanceRegisters;

use App\Filament\Resources\ControlledSubstanceRegisters\Pages\CreateControlledSubstanceRegister;
use App\Filament\Resources\ControlledSubstanceRegisters\Pages\EditControlledSubstanceRegister;
use App\Filament\Resources\ControlledSubstanceRegisters\Pages\ListControlledSubstanceRegisters;
use App\Filament\Resources\ControlledSubstanceRegisters\Pages\ViewControlledSubstanceRegister;
use App\Filament\Resources\ControlledSubstanceRegisters\Schemas\ControlledSubstanceRegisterForm;
use App\Filament\Resources\ControlledSubstanceRegisters\Schemas\ControlledSubstanceRegisterInfolist;
use App\Filament\Resources\ControlledSubstanceRegisters\Tables\ControlledSubstanceRegistersTable;
use App\Models\ControlledSubstanceRegister;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ControlledSubstanceRegisterResource extends Resource
{
    protected static ?string $model = ControlledSubstanceRegister::class;
 protected static ?string $navigationParentItem = 'Pharmacy Management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Controlled Register';

    protected static ?string $recordTitleAttribute = 'inventory_item_id';

    public static function form(Schema $schema): Schema
    {
        return ControlledSubstanceRegisterForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ControlledSubstanceRegisterInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ControlledSubstanceRegistersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListControlledSubstanceRegisters::route('/'),
            'create' => CreateControlledSubstanceRegister::route('/create'),
            'view' => ViewControlledSubstanceRegister::route('/{record}'),
            'edit' => EditControlledSubstanceRegister::route('/{record}/edit'),
        ];
    }
}
