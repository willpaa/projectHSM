<?php

namespace App\Filament\Resources\Appointments\Tables;

use App\Models\Appointments;
use App\Filament\Resources\Appointments\AppointmentsResource;
use App\Services\AppointmentSchedulingService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_id')
                    ->label('Appointment ID')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('patient.full_name')
                    ->label('Patient')
                    ->description(fn (Appointments $record): ?string => $record->patient?->patient_id)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('doctor.full_name')
                    ->label('Doctor')
                    ->description(fn (Appointments $record): ?string => $record->doctor?->specialization)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('token_no')
                    ->label('Queue token')
                    ->placeholder('Not checked in')
                    ->toggleable(),
                TextColumn::make('time')
                    ->time('g:i A')
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Length')
                    ->formatStateUsing(fn (int $state): string => "{$state} min"),
                TextColumn::make('visit_type')
                    ->label('Visit')
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'requested', 'scheduled' => 'warning',
                        'checked_in', 'in_progress' => 'info',
                        'completed' => 'success',
                        'cancelled', 'no_show' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'requested' => 'Requested',
                        'scheduled' => 'Scheduled',
                        'checked_in' => 'Checked in',
                        'in_progress' => 'In progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No show',
                    ]),
                SelectFilter::make('visit_type')
                    ->label('Visit type')
                    ->options(['OPD' => 'Outpatient (OPD)', 'IPD' => 'Inpatient (IPD)']),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Appointments $record): bool => $record->status === 'requested'
                        && AppointmentsResource::canTransition($record, 'scheduled'))
                    ->action(function (Appointments $record, AppointmentSchedulingService $scheduler): void {
                        $scheduler->transition($record, 'scheduled');
                    })
                    ->successNotificationTitle('Appointment confirmed'),
                Action::make('check_in')
                    ->label('Check in')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->visible(fn (Appointments $record): bool => $record->status === 'scheduled'
                        && ($record->date?->isToday() ?? false)
                        && AppointmentsResource::canTransition($record, 'checked_in'))
                    ->action(function (Appointments $record, AppointmentSchedulingService $scheduler): void {
                        $scheduler->transition($record, 'checked_in');
                    })
                    ->successNotificationTitle('Patient checked in'),
                Action::make('start_visit')
                    ->label('Start visit')
                    ->icon('heroicon-o-play')
                    ->visible(fn (Appointments $record): bool => $record->status === 'checked_in'
                        && AppointmentsResource::canTransition($record, 'in_progress'))
                    ->action(function (Appointments $record, AppointmentSchedulingService $scheduler): void {
                        $scheduler->transition($record, 'in_progress');
                    })
                    ->successNotificationTitle('Visit started'),
                Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Appointments $record): bool => $record->status === 'in_progress'
                        && AppointmentsResource::canTransition($record, 'completed'))
                    ->action(function (Appointments $record, AppointmentSchedulingService $scheduler): void {
                        $scheduler->transition($record, 'completed');
                    })
                    ->successNotificationTitle('Appointment completed'),
                Action::make('no_show')
                    ->label('No show')
                    ->icon('heroicon-o-user-minus')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Appointments $record): bool => $record->status === 'scheduled'
                        && AppointmentsResource::canTransition($record, 'no_show'))
                    ->action(function (Appointments $record, AppointmentSchedulingService $scheduler): void {
                        $scheduler->transition($record, 'no_show');
                    })
                    ->successNotificationTitle('Appointment marked as no-show'),
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Appointments $record): bool => in_array($record->status, ['requested', 'scheduled', 'checked_in'], true)
                        && AppointmentsResource::canTransition($record, 'cancelled'))
                    ->action(function (Appointments $record, AppointmentSchedulingService $scheduler): void {
                        $scheduler->transition($record, 'cancelled');
                    })
                    ->successNotificationTitle('Appointment cancelled'),
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Appointments $record): string => AppointmentsResource::getUrl('view', ['record' => $record]))
                    ->visible(fn (Appointments $record): bool => AppointmentsResource::canView($record)),
                EditAction::make()
                    ->visible(fn (Appointments $record): bool => AppointmentsResource::canEdit($record)),
            ])
            ->defaultSort('date')
            ->paginated([10, 25, 50]);
    }
}
