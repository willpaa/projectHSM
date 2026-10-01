<?php

namespace App\Filament\Resources\PatientHistories;

use App\Filament\Resources\PatientHistories\Pages\CreatePatientHistory;
use App\Filament\Resources\PatientHistories\Pages\EditPatientHistory;
use App\Filament\Resources\PatientHistories\Pages\ListPatientHistories;
use App\Filament\Resources\PatientHistories\Schemas\PatientHistoryForm;
use App\Filament\Resources\PatientHistories\Tables\PatientHistoriesTable;
use App\Models\PatientHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PatientHistoryResource extends Resource
{
    protected static ?string $model = PatientHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PatientHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PatientHistoriesTable::configure($table);
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
            'index' => ListPatientHistories::route('/'),
            'create' => CreatePatientHistory::route('/create'),
            'edit' => EditPatientHistory::route('/{record}/edit'),
        ];
    }
}
