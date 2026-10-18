<?php

namespace App\Filament\Widgets;

use App\Models\Laboratory;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use App\Support\ClinicalReportAccess;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RoleDoctorLabResults extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Lab Results')
            ->query(function (): Builder {
                $doctorId = ClinicalReportAccess::currentDoctorId();
                $query = Laboratory::query()->with('patient');

                if ($doctorId === null) {
                    return $query->whereRaw('1 = 0');
                }

                return $query
                    ->where('doctor_id', $doctorId)
                    ->where('status', 'completed')
                    ->latest('result_date')
                    ->limit(8);
            })
            ->columns([
                TextColumn::make('lab_id')->label('Lab ID'),
                TextColumn::make('patient.full_name')->label('Patient'),
                TextColumn::make('test_name')->label('Test'),
                TextColumn::make('result_value')
                    ->label('Result Details')
                    ->state(fn (Laboratory $record): string => self::formatResult($record))
                    ->description(fn (Laboratory $record): ?string => filled($record->notes) ? $record->notes : null)
                    ->wrap(),
                TextColumn::make('normal_range')
                    ->label('Normal Range')
                    ->placeholder('Not provided'),
                TextColumn::make('result_status')
                    ->label('Assessment')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'normal' => 'success',
                        'abnormal' => 'warning',
                        'critical' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('result_date')->label('Date')->date(),
            ])
            ->recordActions([
                ViewAction::make('view_result')
                    ->label('View Result')
                    ->infolist([
                        Section::make('Laboratory Result')
                            ->schema([
                                TextEntry::make('lab_id')->label('Lab ID'),
                                TextEntry::make('patient.full_name')->label('Patient'),
                                TextEntry::make('test_name')->label('Test'),
                                TextEntry::make('result_date')->date()->label('Result Date'),
                                TextEntry::make('result_status')->badge()->label('Assessment'),
                                TextEntry::make('normal_range')->label('Normal Range'),
                                TextEntry::make('result_value')
                                    ->label('Result Details')
                                    ->formatStateUsing(fn (?string $state, Laboratory $record): string => self::formatResult($record))
                                    ->columnSpanFull(),
                                TextEntry::make('notes')->label('Lab Notes')->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
            ])
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('doctor') ?? false;
    }

    private static function formatResult(Laboratory $record): string
    {
        $results = $record->result_data;

        if (! is_array($results) || $results === []) {
            $results = json_decode((string) $record->result_value, true);
        }

        if (! is_array($results) || $results === []) {
            return filled($record->result_value) ? (string) $record->result_value : '-';
        }

        return collect($results)
            ->map(fn (mixed $value, string|int $key): string => is_scalar($value)
                ? "{$key}: {$value}"
                : "{$key}: " . json_encode($value, JSON_UNESCAPED_UNICODE))
            ->implode('; ');
    }
}
