<?php

namespace App\Filament\Resources\FinanceStaff;

use App\Filament\Resources\FinanceStaff\Pages\CreateFinanceStaff;
use App\Filament\Resources\FinanceStaff\Pages\EditFinanceStaff;
use App\Filament\Resources\FinanceStaff\Pages\ListFinanceStaff;
use App\Filament\Resources\FinanceStaff\Pages\ViewFinanceStaff;
use App\Filament\Resources\Staff\Schemas\StaffForm;
use App\Filament\Resources\Staff\Schemas\StaffInfolist;
use App\Filament\Resources\Staff\Tables\StaffTable;
use App\Models\Staff;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinanceStaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationParentItem = 'Staff Management';

    protected static ?string $navigationLabel = 'Finance Staff';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema, 'accountant');
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table, 'accountant');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'accountant');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinanceStaff::route('/'),
            'create' => CreateFinanceStaff::route('/create'),
            'view' => ViewFinanceStaff::route('/{record}'),
            'edit' => EditFinanceStaff::route('/{record}/edit'),
        ];
    }
}
