<?php

namespace App\Support;

use App\Filament\Resources\Radiologies\RadiologyResource;
use App\Models\Radiology;
use App\Models\Service;
use App\Models\ServiceRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RadiologyHeaderActions
{
    private static function radiologySelect(bool $withRequest): Select
    {
        $query = self::radiologiesQuery()->with('patient')->whereNotNull('patient_id');
        $withRequest ? $query->whereNotNull('service_request_id') : $query->whereNull('service_request_id');

        return Select::make('radiology_id')
            ->label('Radiology Request')
            ->options(fn (): array => (clone $query)->orderByDesc('created_at')->get()
                ->mapWithKeys(fn (Radiology $radiology): array => [
                    $radiology->id => trim(($radiology->patient?->full_name ?? 'Patient') . ' — ' . $radiology->exam_name . ' — ' . $radiology->radiology_id),
                ])->all())
            ->default(fn (): ?int => (clone $query)->latest('created_at')->value('id'))
            ->searchable()->preload()->required();
    }

    private static function radiologiesQuery(): Builder
    {
        return RadiologyResource::getEloquentQuery();
    }

    public static function sendToCashier(): Action
    {
        return Action::make('send_radiology_to_cashier_header')
            ->label('Send to Cashier')->icon('heroicon-o-banknotes')->color('warning')
            ->modalHeading('Create Radiology Payment Request')
            ->modalDescription('Select the imaging request and active radiology tariff. The cashier collects payment.')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->form([
                self::radiologySelect(false),
                Select::make('service_id')->label('Radiology Service')
                    ->options(fn (): array => Service::radiologyServices()->orderBy('name')->get()
                        ->mapWithKeys(fn (Service $service): array => [$service->id => $service->name . ' - ETB ' . number_format((float) $service->price, 2)])->all())
                    ->searchable()->preload()
                    ->default(fn (): ?int => Service::radiologyServices()->count() === 1 ? Service::radiologyServices()->value('id') : null)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $record = self::radiologiesQuery()->whereNull('service_request_id')->findOrFail($data['radiology_id']);
                abort_unless(! PatientPortal::isPatientUser() && filled($record->patient_id), 403);
                $service = Service::radiologyServices()->findOrFail($data['service_id']);
                $serviceRequest = ServiceRequest::create([
                    'request_number' => 'SR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                    'patient_id' => $record->patient_id,
                    'encounter_type' => $record->encounter_type,
                    'encounter_id' => $record->encounter_id,
                    'service_id' => $service->id,
                    'requested_by' => Auth::id(),
                    'payment_status' => 'pending',
                    'fulfillment_status' => 'requested',
                    'total_amount' => $service->price,
                    'patient_share' => $service->calculatePatientShare($record->patient),
                    'insurance_share' => $service->calculateInsuranceShare($record->patient),
                    'discount' => 0,
                    'paid_amount' => 0,
                    'requested_at' => now(),
                    'notes' => 'Radiology request. Patient must clear payment before imaging.',
                ]);
                $record->update(['service_request_id' => $serviceRequest->id, 'status' => 'ordered']);

                Notification::make()->title('Patient sent to cashier.')->body('Request: ' . $serviceRequest->request_number . '. Amount due: ETB ' . number_format((float) $serviceRequest->patient_share, 2))->success()->send();
            });
    }

    public static function resendToCashier(): Action
    {
        return Action::make('resend_radiology_to_cashier_header')
            ->label('Resend to Cashier')->icon('heroicon-o-arrow-path')->color('info')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->form([self::radiologySelect(true)])
            ->action(function (array $data): void {
                $record = self::radiologiesQuery()->with('serviceRequest')->whereNotNull('service_request_id')->findOrFail($data['radiology_id']);
                abort_unless(! PatientPortal::isPatientUser(), 403);
                if ($record->serviceRequest?->payment_status === 'paid') {
                    Notification::make()->title('This request is already paid.')->success()->send();
                    return;
                }
                ServiceRequestNotifier::notifyCashiers($record->serviceRequest);
                Notification::make()->title('Cashier notification sent again.')->body('Request: ' . $record->serviceRequest?->request_number)->success()->send();
            });
    }
}
