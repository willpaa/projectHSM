<?php

namespace App\Support;

use App\Filament\Resources\Laboratories\LaboratoryResource;
use App\Models\Laboratory;
use App\Models\Service;
use App\Models\ServiceRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LaboratoryHeaderActions
{
    private static function laboratorySelect(bool $withRequest): Select
    {
        $query = self::laboratoriesQuery()->with('patient')->whereNotNull('patient_id');
        $withRequest ? $query->whereNotNull('service_request_id') : $query->whereNull('service_request_id');

        return Select::make('laboratory_id')
            ->label('Laboratory Request')
            ->options(fn (): array => (clone $query)->orderByDesc('created_at')->get()
                ->mapWithKeys(fn (Laboratory $lab): array => [
                    $lab->id => trim(($lab->patient?->full_name ?? 'Patient') . ' — ' . $lab->test_name . ' — ' . $lab->lab_id),
                ])->all())
            ->default(fn (): ?int => (clone $query)->latest('created_at')->value('id'))
            ->searchable()->preload()->required();
    }

    private static function laboratoriesQuery(): Builder
    {
        return LaboratoryResource::getEloquentQuery();
    }

    public static function sendToCashier(): Action
    {
        return Action::make('send_laboratory_to_cashier_header')
            ->label('Send to Cashier')->icon('heroicon-o-banknotes')->color('warning')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->form([
                self::laboratorySelect(false),
                Select::make('service_id')->label('Lab Service')
                    ->options(fn (): array => Service::query()->where('service_type', 'lab')->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()->preload()->required(),
            ])
            ->action(function (array $data): void {
                $record = self::laboratoriesQuery()->whereNull('service_request_id')->findOrFail($data['laboratory_id']);
                abort_unless(! PatientPortal::isPatientUser() && filled($record->patient_id), 403);
                $service = Service::query()->where('service_type', 'lab')->where('is_active', true)->findOrFail($data['service_id']);
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
                    'notes' => 'Laboratory request. Patient must clear payment before sample processing.',
                ]);
                $record->update(['service_request_id' => $serviceRequest->id, 'cost' => $service->price, 'status' => 'ordered']);

                Notification::make()->title('Patient sent to cashier.')->body("Request: {$serviceRequest->request_number}")->success()->send();
            });
    }

    public static function resendToCashier(): Action
    {
        return Action::make('resend_laboratory_to_cashier_header')
            ->label('Resend to Cashier')->icon('heroicon-o-arrow-path')->color('info')
            ->visible(fn (): bool => ! PatientPortal::isPatientUser())
            ->form([self::laboratorySelect(true)])
            ->action(function (array $data): void {
                $record = self::laboratoriesQuery()->with('serviceRequest')->whereNotNull('service_request_id')->findOrFail($data['laboratory_id']);
                abort_unless(! PatientPortal::isPatientUser(), 403);
                if ($record->serviceRequest?->payment_status === 'paid') {
                    Notification::make()->title('This request is already paid.')->success()->send();
                    return;
                }
                ServiceRequestNotifier::notifyCashiers($record->serviceRequest);
                Notification::make()->title('Cashier notification sent again.')->body("Request: {$record->serviceRequest?->request_number}")->success()->send();
            });
    }
}
