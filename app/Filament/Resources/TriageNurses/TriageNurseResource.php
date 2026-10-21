<?php

namespace App\Filament\Resources\TriageNurses;

use App\Filament\Resources\Nurses\Schemas\NurseForm;
use App\Filament\Resources\Nurses\Schemas\NurseInfolist;
use App\Filament\Resources\Nurses\Tables\NursesTable;
use App\Filament\Resources\TriageNurses\Pages\CreateTriageNurse;
use App\Filament\Resources\TriageNurses\Pages\EditTriageNurse;
use App\Filament\Resources\TriageNurses\Pages\ListTriageNurses;
use App\Filament\Resources\TriageNurses\Pages\ViewTriageNurse;
use App\Models\Nurse;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TriageNurseResource extends Resource
{
    protected static ?string $model = Nurse::class;

    protected static ?string $navigationParentItem = 'Staff Management';

    protected static ?string $navigationLabel = 'Triage Nurses';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return NurseForm::configure($schema, 'triage_nurse');
    }

    public static function infolist(Schema $schema): Schema
    {
        return NurseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NursesTable::configure($table, 'triage_nurse');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'department'])->where('role', 'triage_nurse');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTriageNurses::route('/'),
            'create' => CreateTriageNurse::route('/create'),
            'view' => ViewTriageNurse::route('/{record}'),
            'edit' => EditTriageNurse::route('/{record}/edit'),
        ];
    }
}
