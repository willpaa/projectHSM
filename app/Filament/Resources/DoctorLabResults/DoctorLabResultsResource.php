<?php

namespace App\Filament\Resources\DoctorLabResults;

use App\Filament\Resources\DoctorLabResults\Pages\ListDoctorLabResults;
use App\Models\Laboratory;
use App\Support\ClinicalReportAccess;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DoctorLabResultsResource extends Resource
{
    protected static ?string $model = Laboratory::class;

    protected static string|\UnitEnum|null $navigationGroup = null;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Lab Results';

    protected static ?string $modelLabel = 'Lab Result';

    protected static ?string $pluralModelLabel = 'Lab Results';

    public static function canViewAny(): bool
    {
        return ClinicalReportAccess::isDoctorUser();
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
        return parent::getEloquentQuery()
            ->where('doctor_id', ClinicalReportAccess::currentDoctorId())
            ->where('status', 'completed');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Laboratory Result')
                ->schema([
                    TextEntry::make('lab_id')->label('Lab ID'),
                    TextEntry::make('patient.full_name')->label('Patient'),
                    TextEntry::make('test_name')->label('Test'),
                    TextEntry::make('result_date')->date()->label('Result Date'),
                    TextEntry::make('result_status')->badge()->label('Assessment'),
                    TextEntry::make('normal_range')->label('Normal Range'),
                    TextEntry::make('result_value')
                        ->label('Result Values')
                        ->formatStateUsing(fn (?string $state, Laboratory $record): string => self::formatResult($record, $state))
                        ->columnSpanFull(),
                    TextEntry::make('notes')->label('Lab Notes')->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lab_id')->label('Lab ID')->searchable(),
                TextColumn::make('patient.full_name')->label('Patient')->searchable(),
                TextColumn::make('test_name')->label('Test')->searchable(),
                TextColumn::make('result_value')
                    ->label('Result')
                    ->state(fn (Laboratory $record): string => self::formatResult($record, $record->result_value))
                    ->wrap(),
                TextColumn::make('result_status')->label('Assessment')->badge(),
                TextColumn::make('result_date')->label('Date')->date()->sortable(),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('result_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctorLabResults::route('/'),
        ];
    }

    private static function formatResult(Laboratory $record, ?string $state): string
    {
        $results = $record->result_data;

        if (! is_array($results) || $results === []) {
            $results = json_decode((string) $state, true);
        }

        if (! is_array($results) || $results === []) {
            return filled($state) ? $state : '-';
        }

        return collect($results)
            ->map(fn (mixed $value, string|int $key): string => is_scalar($value)
                ? "{$key}: {$value}"
                : "{$key}: " . json_encode($value, JSON_UNESCAPED_UNICODE))
            ->implode('; ');
    }
}
