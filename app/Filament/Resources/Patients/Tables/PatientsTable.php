<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Filament\Support\TableFilters;
use App\Models\Patients;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use App\Models\Triage;
use App\Models\User;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no')
                    ->label('#')
                    ->rowIndex(),
                TextColumn::make('patient_id')
                    ->label('Patient Code')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('mrn')
                    ->label('MRN')
                    ->searchable(),
                TextColumn::make('full_name')
                    ->label('Patient Name')
                    ->searchable(),
                TextColumn::make('gender')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('dob')
                    ->date()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('address')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('blood_group')
                    ->badge()
                    ->color('danger'),
                TextColumn::make('allergies')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('emergency_contact')
                    ->searchable(),
                TextColumn::make('insurance_id')
                    ->label('Insurance')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('registered_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'deceased' => 'Deceased',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'today' => 'warning',
                        'active' => 'success',
                        'inactive' => 'gray',
                        'deceased' => 'danger',
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
            ->filters([
                TableFilters::select('gender', [
                    'male' => 'Male',
                    'female' => 'Female',
                    'other' => 'Other',
                ]),
                TableFilters::distinct('patient_id', Patients::class, 'Patient Code'),
                 TableFilters::distinct('mrn', Patients::class, 'MRN'),
                TableFilters::select('blood_group', [
                    'A+' => 'A+',
                    'A-' => 'A-',
                    'B+' => 'B+',
                    'B-' => 'B-',
                    'AB+' => 'AB+',
                    'AB-' => 'AB-',
                    'O+' => 'O+',
                    'O-' => 'O-',
                ]),
                TableFilters::select('status', [
                    'active' => 'Active',
                    'today' => 'Today',
                    'inactive' => 'Inactive',
                    'deceased' => 'Deceased',
                ]),
                TableFilters::distinct('insurance_id', Patients::class, 'Insurance'),
                TableFilters::dateRange('registered_at', 'Registered Date'),
                TableFilters::dateRange('dob', 'Date of Birth'),
                TableFilters::createdAt(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
   
            Action::make('send_to_triage')
                ->label('Send to Triage')
                ->icon('heroicon-o-clipboard-document-list')
                ->color('warning')
                ->visible(fn (): bool => auth()->user()?->hasAnyRole([
                    'admin', 'super_admin', 'receptionist', 'opd_staff', 'triage_nurse',
                ]) ?? false)
                ->action(function (Patients $record): void {
                    $pendingTriageExists = Triage::query()
                        ->where('patient_id', $record->id)
                        ->whereIn('status', ['waiting', 'in_progress'])
                        ->exists();

                    if ($pendingTriageExists) {
                        Notification::make()
                            ->title('Patient already exists in triage queue.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $triageId = static::generateTriageId();

                    Triage::create([
                        'triage_id' => $triageId,
                        'patient_id' => $record->id,
                        'priority' => 'medium',
                        'status' => 'waiting',
                    ]);

                    $record->update([
                        'status' => 'today',
                    ]);

                    $triageUsers = User::usersByRoleName('triage_nurse');
                    if ($triageUsers->isNotEmpty()) {
                        Notification::make()
                            ->title('New Triage Queue')
                            ->body("Patient {$record->full_name} sent to Triage.")
                            ->icon('heroicon-o-bell-alert')
                            ->sendToDatabase($triageUsers);
                    }

                    Notification::make()
                        ->title('Patient sent to Triage successfully.')
                        ->body("Triage ID: {$triageId}")
                        ->success()
                        ->send();
                }),
                 
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    protected static function generateTriageId(): string
    {
        do {
            $triageId = 'TRG-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Triage::where('triage_id', $triageId)->exists());

        return $triageId;
    }
}
