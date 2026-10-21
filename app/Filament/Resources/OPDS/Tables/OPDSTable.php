<?php

namespace App\Filament\Resources\OPDS\Tables;

use App\Filament\Support\TableFilters;
use App\Support\PatientPortal;
use App\Support\ClinicalServiceRequestAction;
use App\Support\OPDHeaderActions;
use App\Models\OPD;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
class OPDSTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('arrival_flag')
                    ->label('Arrival')
                    ->state(fn (OPD $record): string => $record->created_at?->isToday() ? 'New Today' : 'Old')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'New Today' ? 'success' : 'gray'),
                TextColumn::make('encounter_id')
                    ->searchable(),
                TextColumn::make('patient.patient_id')
                    ->label('Patient ID')
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient Name')
                    ->searchable(),
                TextColumn::make('patient.phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('doctor.full_name')
                    ->label('Doctor')
                    ->searchable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('icd10_code')
                    ->label('ICD-10')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('admission_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('discharge_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('follow_up_date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bed.bed_id')
                    ->label('Bed')
                    ->searchable(),
                TextColumn::make('prescription_id')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'New Patient',
                        'closed' => 'Closed',
                        'transferred' => 'Transferred',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'warning',
                        'closed' => 'success',
                        'transferred' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->filters([
                TableFilters::relationship('patient', 'full_name', 'Patient'),
                TableFilters::relationship('doctor', 'full_name', 'Doctor'),
                TableFilters::distinct('department', OPD::class, 'Department'),
                TableFilters::distinct('bed_id', OPD::class, 'Bed'),
                TableFilters::select('type', [
                    'OPD' => 'OPD',
                    'IPD' => 'IPD',
                ]),
                TableFilters::select('status', [
                    'open' => 'Open',
                    'closed' => 'Closed',
                    'transferred' => 'Transferred',
                ]),
                TableFilters::dateRange('admission_date', 'Admission Date'),
                TableFilters::dateRange('discharge_date', 'Discharge Date'),
                TableFilters::dateRange('follow_up_date', 'Follow-up Date'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->headerActions([
                ClinicalServiceRequestAction::makeHeader('OPD'),
                OPDHeaderActions::sendToIpd(),
                OPDHeaderActions::callPatient(),
                OPDHeaderActions::sendToLab(),
                OPDHeaderActions::sendToRadiology(),
            ])
            ->recordActions([
                Action::make('discharge_patient')
                    ->label('Discharge')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (OPD $record): bool => ! PatientPortal::isPatientUser() && $record->status === 'open')
                    ->form([
                        Textarea::make('discharge_summary')
                            ->label('Discharge Summary')
                            ->required()
                            ->rows(3),
                        DatePicker::make('follow_up_date')
                            ->label('Follow-up Date'),
                    ])
                    ->action(function (OPD $record, array $data): void {
                        $record->update([
                            'status' => 'closed',
                            'discharge_date' => now()->toDateString(),
                            'discharge_summary' => $data['discharge_summary'],
                            'follow_up_date' => $data['follow_up_date'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Patient discharged from OPD.')
                            ->success()
                            ->send();
                    }),
                
                ViewAction::make(),
                EditAction::make(),
            ], RecordActionsPosition::AfterColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
