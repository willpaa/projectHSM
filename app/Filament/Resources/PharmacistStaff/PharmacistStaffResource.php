<?php

namespace App\Filament\Resources\PharmacistStaff;

use App\Filament\Resources\PharmacistStaff\Pages\CreatePharmacistStaff;
use App\Filament\Resources\PharmacistStaff\Pages\EditPharmacistStaff;
use App\Filament\Resources\PharmacistStaff\Pages\ListPharmacistStaff;
use App\Filament\Resources\PharmacistStaff\Pages\ViewPharmacistStaff;
use App\Filament\Resources\Staff\Schemas\StaffForm;
use App\Filament\Resources\Staff\Schemas\StaffInfolist;
use App\Filament\Resources\Staff\Tables\StaffTable;
use App\Models\Staff;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PharmacistStaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationParentItem = 'Staff Management';

    protected static ?string $navigationLabel = 'Pharmacists';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema, 'pharmacist');
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table, 'pharmacist');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'pharmacist');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPharmacistStaff::route('/'),
            'create' => CreatePharmacistStaff::route('/create'),
            'view' => ViewPharmacistStaff::route('/{record}'),
            'edit' => EditPharmacistStaff::route('/{record}/edit'),
        ];
    }
}
