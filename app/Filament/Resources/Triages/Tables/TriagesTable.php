<?php

namespace App\Filament\Resources\Triages\Tables;

use App\Filament\Resources\OPDS\OPDResource;
use App\Filament\Support\TableFilters;
use App\Models\Doctors;
use App\Models\Laboratory;
use App\Models\OPD;
use App\Models\IPD;
use App\Models\Radiology;
use App\Models\Triage;
use App\Models\User;
use App\Support\ClinicalNurseAccess;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class TriagesTable
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
                    ->state(fn (Triage $record): string => $record->created_at?->isToday() ? 'New Today' : 'Old')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'New Today' ? 'success' : 'gray'),
                TextColumn::make('triage_id')
                    ->searchable(),
                TextColumn::make('patient.patient_id')
                    ->label('Patient ID')
                    ->searchable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->searchable(),
                TextColumn::make('priority')
                    ->badge(),
                TextColumn::make('chief_complaint')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('blood_pressure')->label('BP')->toggleable(),
                TextColumn::make('pulse_rate')->label('Pulse')->toggleable(),
                TextColumn::make('oxygen_saturation')->label('SpO₂')->toggleable(),
                TextColumn::make('nurse.full_name')
                    ->label('Nurse')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TableFilters::relationship('patient', 'full_name', 'Patient'),
                TableFilters::relationship('nurse', 'full_name', 'Nurse'),
                TableFilters::select('priority', [
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'critical' => 'Critical',
                ]),
                TableFilters::select('status', [
                    'waiting' => 'Waiting',
                    'in_progress' => 'In Progress',
                    'completed' => 'Completed',
                    'sent_to_opd' => 'Sent to OPD',
                ]),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                Action::make('claim')
                    ->label('Take Case')
                    ->icon('heroicon-o-hand-raised')
                    ->color('primary')
                    ->visible(fn (Triage $record): bool => ClinicalNurseAccess::isTriageNurse()
                        && $record->nurse_id === null
                        && in_array($record->status, ['waiting', 'in_progress'], true))
                    ->action(function (Triage $record): void {
                        $nurseId = ClinicalNurseAccess::currentNurseId('triage_nurse');

                        if ($nurseId === null) {
                            Notification::make()
                                ->title('Your account is not linked to a Triage Nurse profile.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $claimed = DB::transaction(function () use ($record, $nurseId): bool {
                            $triage = Triage::query()->lockForUpdate()->find($record->id);

                            if (! $triage || $triage->nurse_id !== null
                                || ! in_array($triage->status, ['waiting', 'in_progress'], true)) {
                                return false;
                            }

                            $triage->update([
                                'nurse_id' => $nurseId,
                                'status' => 'in_progress',
                            ]);

                            return true;
                        });

                        Notification::make()
                            ->title($claimed ? 'Triage case assigned to you.' : 'Another triage nurse already took this case.')
                            ->status($claimed ? 'success' : 'warning')
                            ->send();
                    }),
                Action::make('send_to_opd')
                    ->label('Send to OPD')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->visible(fn (Triage $record): bool => in_array($record->status, ['waiting', 'in_progress'], true)
                        && ClinicalNurseAccess::canAccessTriage($record))
                    ->form([
                        Select::make('doctor_id')
                            ->label('Doctor')
                            ->options(
                                Doctors::query()
                                    ->where('status', 'active')
                                    ->pluck('full_name', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Triage $record, array $data): void {
                        $existingOpenOpd = OPD::query()
                            ->where('patient_id', $record->patient_id)
                            ->where('status', 'open')
                            ->exists();

                        if ($existingOpenOpd) {
                            Notification::make()
                                ->title('Patient already has an open OPD encounter.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $encounterId = 'OPD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

                        OPD::create([
                            'encounter_id' => $encounterId,
                            'patient_id' => $record->patient_id,
                            'type' => 'OPD',
                            'doctor_id' => $data['doctor_id'],
                            'status' => 'open',
                        ]);

                        $record->update([
                            'status' => 'sent_to_opd',
                            'encounter_id' => $encounterId,
                        ]);

                        $opdUsers = User::usersByRoleName('opd_staff');
                        if ($opdUsers->isNotEmpty()) {
                            Notification::make()
                                ->title('New OPD Queue')
                                ->body("Triage patient {$record->patient?->full_name} sent to OPD.")
                                ->icon('heroicon-o-bell-alert')
                                ->sendToDatabase($opdUsers);
                        }

                        // Notify the doctor assigned to this encounter so the patient
                        // appears in their account with a direct link to the OPD record.
                        $assignedDoctor = Doctors::query()->with('user')->find($data['doctor_id']);
                        if ($assignedDoctor?->user) {
                            Notification::make()
                                ->title('New OPD Patient Assigned')
                                ->body("Patient {$record->patient?->full_name} has been assigned to you in OPD.")
                                ->icon('heroicon-o-user-plus')
                                ->actions([
                                    \Filament\Actions\Action::make('view')
                                        ->label('View Patient')
                                        ->url(OPDResource::getUrl('index')),
                                ])
                                ->sendToDatabase($assignedDoctor->user);
                        }

                        Notification::make()
                            ->title('Patient sent to OPD successfully.')
                            ->body("Encounter ID: {$encounterId}")
                            ->success()
                            ->send();
                    }),
                   
                    

               

                       
                ViewAction::make(),
                EditAction::make(),
                 DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasAnyRole(['admin', 'super_admin']) ?? false),
                ]),
            ]);
    }
   
}
