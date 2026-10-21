<?php

namespace App\Support;

use App\Models\IPD;
use App\Models\OPD;
use App\Models\Service;
use App\Models\ServiceRequest;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClinicalServiceRequestAction
{
    private static function canUseTable(?Model $record = null): bool
    {
        $user = auth()->user();

        if (! $user || PatientPortal::isPatientUser()) {
            return false;
        }

        if ($record && ClinicalReportAccess::canViewReport($record)) {
            return true;
        }

        return ClinicalReportAccess::isDoctorUser()
            || $user->hasAnyRole(['admin', 'super_admin', 'opd_staff', 'ipd_staff']);
    }

    public static function makeHeader(string $encounterType): Action
    {
        $modelClass = $encounterType === 'IPD' ? IPD::class : OPD::class;

        return Action::make('send_to_finance_header')
            ->label('Send to Finance')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->button()
            ->modalHeading("Send {$encounterType} service request to Finance")
            ->modalDescription('Choose the encounter, service, and clinical reason. Finance will review the patient and insurance shares.')
            ->visible(fn (): bool => static::canUseTable())
            ->form([
                Select::make('encounter_id')
                    ->label($encounterType . ' patient / encounter')
                    ->options(fn (): array => static::openEncounterQuery($modelClass, $encounterType)
                        ->with(['patient', 'doctor'])
                        ->orderByDesc('created_at')
                        ->get()
                        ->mapWithKeys(fn (Model $record): array => [
                            $record->encounter_id => sprintf(
                                '%s — %s — Dr. %s',
                                $record->patient?->full_name ?? 'Unknown patient',
                                $record->encounter_id,
                                $record->doctor?->full_name ?? 'Unassigned',
                            ),
                        ])
                        ->all())
                    ->searchable()
                    ->required(),
                Select::make('service_id')
                    ->label('Requested service')
                    ->options(fn (): array => static::serviceOptions())
                    ->searchable()
                    ->required()
                    ->helperText('The configured tariff and insurance coverage are used.'),
                Textarea::make('notes')
                    ->label('Clinical reason / instructions')
                    ->rows(3)
                    ->maxLength(2000),
            ])
            ->action(function (array $data) use ($modelClass, $encounterType): void {
                $record = static::openEncounterQuery($modelClass, $encounterType)
                    ->where('encounter_id', $data['encounter_id'])
                    ->firstOrFail();

                if (! static::canUseTable($record)) {
                    abort(403);
                }

                static::createRequest($record, (int) $data['service_id'], $data['notes'] ?? null);
            });
    }

    /** @param class-string<OPD|IPD> $modelClass */
    private static function openEncounterQuery(string $modelClass, string $encounterType): Builder
    {
        $query = $modelClass::query()->whereIn(
            'status',
            $encounterType === 'IPD' ? ['admitted', 'transferred'] : ['open'],
        );

        $user = auth()->user();
        if (! $user?->hasAnyRole(['admin', 'super_admin']) && ClinicalReportAccess::isDoctorUser()) {
            $doctorId = ClinicalReportAccess::currentDoctorId();

            return $doctorId
                ? $query->where('doctor_id', $doctorId)
                : $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /** @return array<int, string> */
    private static function serviceOptions(): array
    {
        return Service::query()
            ->where('is_active', true)
            // Lab and radiology referrals create their own finance requests.
            ->whereNotIn('service_type', ['lab', 'radiology', 'pharmacy'])
            ->orderBy('service_type')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Service $service): array => [
                $service->id => sprintf('%s — %s (ETB %s)', str($service->service_type)->replace('_', ' ')->title(), $service->name, number_format((float) $service->price, 2)),
            ])
            ->all();
    }

    private static function createRequest(Model $record, int $serviceId, ?string $notes = null): void
    {
        if (! $record instanceof OPD && ! $record instanceof IPD) {
            abort(403);
        }

        if (! static::canUseTable($record)) {
            abort(403);
        }

        if (! $record->patient) {
            Notification::make()->title('This encounter has no patient.')->danger()->send();
            return;
        }

        $service = Service::query()
            ->where('is_active', true)
            ->whereNotIn('service_type', ['lab', 'radiology', 'pharmacy'])
            ->findOrFail($serviceId);

        $request = DB::transaction(fn (): ServiceRequest => ServiceRequest::create([
            'request_number' => 'SR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(6)),
            'patient_id' => $record->patient_id,
            'encounter_type' => $record instanceof IPD ? 'IPD' : 'OPD',
            'encounter_id' => $record->encounter_id,
            'service_id' => $service->id,
            'requested_by' => auth()->id(),
            'payment_status' => 'pending',
            'fulfillment_status' => 'requested',
            'total_amount' => $service->price,
            'patient_share' => $service->calculatePatientShare($record->patient),
            'insurance_share' => $service->calculateInsuranceShare($record->patient),
            'discount' => 0,
            'paid_amount' => 0,
            'requested_at' => now(),
            'notes' => filled($notes) ? trim($notes) : null,
        ]));

        Notification::make()
            ->title('Service request sent to Finance')
            ->body("{$request->request_number} — {$service->name}. Patient share: ETB " . number_format((float) $request->patient_share, 2))
            ->success()
            ->send();
    }
}
