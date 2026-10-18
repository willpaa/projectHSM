<?php

namespace App\Filament\Resources\DoctorRadiologyResults;

use App\Filament\Resources\DoctorRadiologyResults\Pages\ListDoctorRadiologyResults;
use App\Models\Radiology;
use App\Support\ClinicalReportAccess;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DoctorRadiologyResultsResource extends Resource
{
    protected static ?string $model = Radiology::class;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Radiology Results';

    protected static ?string $modelLabel = 'Radiology Result';

    protected static ?string $pluralModelLabel = 'Radiology Results';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('doctor') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $doctorId = ClinicalReportAccess::currentDoctorId();

        if ($doctorId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where('doctor_id', $doctorId)
            ->where('status', 'completed');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Radiology Result')
                ->schema([
                    TextEntry::make('radiology_id')->label('Radiology ID'),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('exam_name')->label('Examination'),
                    TextEntry::make('modality')->label('Modality')->placeholder('-'),
                    TextEntry::make('completed_at')->label('Result Date')->dateTime(),
                    TextEntry::make('result_status')->label('Assessment')->badge(),
                    TextEntry::make('result_summary')->label('Summary')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('findings')->label('Findings')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('conclusion')->label('Conclusion')->placeholder('-')->columnSpanFull(),
                    ImageEntry::make('report_image')->label('Report Image')->size(400)->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('radiology_id')->label('Radiology ID')->searchable(),
                TextColumn::make('patient.full_name')->label('Patient')->searchable(),
                TextColumn::make('exam_name')->label('Examination')->searchable(),
                TextColumn::make('modality')->label('Modality')->placeholder('-'),
                TextColumn::make('result_summary')->label('Summary')->placeholder('-')->wrap(),
                TextColumn::make('result_status')->label('Assessment')->badge(),
                TextColumn::make('completed_at')->label('Result Date')->dateTime()->sortable(),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('completed_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctorRadiologyResults::route('/'),
        ];
    }
}
