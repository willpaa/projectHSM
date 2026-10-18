<?php

namespace App\Filament\Resources\RadiologyTechnicians;

use App\Filament\Resources\RadiologyTechnicians\Pages\CreateRadiologyTechnician;
use App\Filament\Resources\RadiologyTechnicians\Pages\EditRadiologyTechnician;
use App\Filament\Resources\RadiologyTechnicians\Pages\ListRadiologyTechnicians;
use App\Filament\Resources\RadiologyTechnicians\Pages\ViewRadiologyTechnician;
use App\Filament\Resources\Staff\Schemas\StaffForm;
use App\Filament\Resources\Staff\Schemas\StaffInfolist;
use App\Filament\Resources\Staff\Tables\StaffTable;
use App\Models\Staff;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RadiologyTechnicianResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationParentItem = 'Staff Management';

    protected static ?string $navigationLabel = 'Radiology Technicians';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema, 'radiology_technician');
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table, 'radiology_technician');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'radiology_technician');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRadiologyTechnicians::route('/'),
            'create' => CreateRadiologyTechnician::route('/create'),
            'view' => ViewRadiologyTechnician::route('/{record}'),
            'edit' => EditRadiologyTechnician::route('/{record}/edit'),
        ];
    }
}
