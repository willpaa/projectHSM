<?php

namespace App\Filament\Resources\LabTechnicians;

use App\Filament\Resources\LabTechnicians\Pages\CreateLabTechnician;
use App\Filament\Resources\LabTechnicians\Pages\EditLabTechnician;
use App\Filament\Resources\LabTechnicians\Pages\ListLabTechnicians;
use App\Filament\Resources\LabTechnicians\Pages\ViewLabTechnician;
use App\Filament\Resources\Staff\Schemas\StaffForm;
use App\Filament\Resources\Staff\Schemas\StaffInfolist;
use App\Filament\Resources\Staff\Tables\StaffTable;
use App\Models\Staff;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LabTechnicianResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationParentItem = 'Staff Management';

    protected static ?string $navigationLabel = 'Lab Technicians';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return StaffForm::configure($schema, 'lab_technician');
    }

    public static function infolist(Schema $schema): Schema
    {
        return StaffInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StaffTable::configure($table, 'lab_technician');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'lab_technician');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLabTechnicians::route('/'),
            'create' => CreateLabTechnician::route('/create'),
            'view' => ViewLabTechnician::route('/{record}'),
            'edit' => EditLabTechnician::route('/{record}/edit'),
        ];
    }
}
