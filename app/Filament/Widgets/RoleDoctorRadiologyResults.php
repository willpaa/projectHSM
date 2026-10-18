<?php

namespace App\Filament\Widgets;

use App\Models\Radiology;
use App\Support\ClinicalReportAccess;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RoleDoctorRadiologyResults extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '30s';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Radiology Results')
            ->query(function (): Builder {
                $doctorId = ClinicalReportAccess::currentDoctorId();
                $query = Radiology::query()->with('patient');

                if ($doctorId === null) {
                    return $query->whereRaw('1 = 0');
                }

                return $query
                    ->where('doctor_id', $doctorId)
                    ->where('status', 'completed')
                    ->latest('completed_at')
                    ->limit(8);
            })
            ->columns([
                TextColumn::make('radiology_id')->label('Radiology ID'),
                TextColumn::make('patient.full_name')->label('Patient'),
                TextColumn::make('exam_name')->label('Examination'),
                TextColumn::make('modality')->label('Modality')->placeholder('-'),
                TextColumn::make('result_summary')
                    ->label('Summary')
                    ->placeholder(fn (Radiology $record): string => filled($record->findings) ? $record->findings : '-')
                    ->wrap(),
                TextColumn::make('result_status')
                    ->label('Assessment')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'normal' => 'success',
                        'abnormal' => 'warning',
                        'critical' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('completed_at')->label('Result Date')->dateTime(),
            ])
            ->recordActions([
                ViewAction::make('view_result')
                    ->label('View Result')
                    ->infolist([
                        Section::make('Radiology Result')
                            ->schema([
                                TextEntry::make('radiology_id')->label('Radiology ID'),
                                TextEntry::make('patient.full_name')->label('Patient'),
                                TextEntry::make('exam_name')->label('Examination'),
                                TextEntry::make('modality')->label('Modality'),
                                TextEntry::make('completed_at')->label('Result Date')->dateTime(),
                                TextEntry::make('result_status')->label('Assessment')->badge(),
                                TextEntry::make('result_summary')->label('Summary')->placeholder('-')->columnSpanFull(),
                                TextEntry::make('findings')->label('Findings')->placeholder('-')->columnSpanFull(),
                                TextEntry::make('conclusion')->label('Conclusion')->placeholder('-')->columnSpanFull(),
                                ImageEntry::make('report_image')->label('Report Image')->size(400)->columnSpanFull(),
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
}
