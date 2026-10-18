<?php

namespace App\Services;

use App\Models\Appointments;
use App\Models\Doctors;
use App\Models\Patients;
use App\Support\ClinicalReportAccess;
use App\Support\PatientPortal;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentSchedulingService
{
    /** @param array<string, mixed> $data */
    public function create(array $data, int $userId, bool $patientBooking = false): Appointments
    {
        return DB::transaction(function () use ($data, $userId, $patientBooking): Appointments {
            if (auth()->user()?->hasRole('doctor')) {
                $doctorId = ClinicalReportAccess::currentDoctorId();

                if ($doctorId === null) {
                    throw ValidationException::withMessages(['doctor_id' => 'Your doctor profile is not linked to this account.']);
                }

                $data['doctor_id'] = $doctorId;
            }

            if ($patientBooking) {
                $patientId = PatientPortal::currentPatientId();

                if ($patientId === null) {
                    throw ValidationException::withMessages(['patient_id' => 'Your patient profile is not linked to this account.']);
                }

                $data['patient_id'] = $patientId;
            }

            $doctor = Doctors::query()->whereKey($data['doctor_id'])->lockForUpdate()->firstOrFail();
            Patients::query()->whereKey($data['patient_id'])->lockForUpdate()->firstOrFail();

            $this->ensureDoctorCanBeBooked($doctor);
            $this->ensureSlotIsAvailable($data);
            $this->ensureDoctorAvailability($doctor, $data);

            return Appointments::query()->create([
                ...$data,
                'status' => $patientBooking ? 'requested' : 'scheduled',
                'created_by' => (string) $userId,
            ]);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Appointments $appointment, array $data): Appointments
    {
        return DB::transaction(function () use ($appointment, $data): Appointments {
            $lockedAppointment = Appointments::query()
                ->whereKey($appointment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedAppointment->status, ['requested', 'scheduled'], true)) {
                throw ValidationException::withMessages(['status' => 'Only requested or scheduled appointments can be rescheduled.']);
            }

            $doctor = Doctors::query()->whereKey($data['doctor_id'])->lockForUpdate()->firstOrFail();
            Patients::query()->whereKey($data['patient_id'])->lockForUpdate()->firstOrFail();

            $this->ensureDoctorCanBeBooked($doctor);
            $this->ensureSlotIsAvailable($data, $lockedAppointment);
            $this->ensureDoctorAvailability($doctor, $data);

            $lockedAppointment->fill($data);
            $lockedAppointment->save();

            return $lockedAppointment;
        });
    }

    public function transition(Appointments $appointment, string $nextStatus): void
    {
        $isOwnPatientCancellation = $nextStatus === 'cancelled'
            && PatientPortal::isPatientUser()
            && (int) $appointment->patient_id === (int) PatientPortal::currentPatientId();

        abort_unless($isOwnPatientCancellation || auth()->user()?->can('Update:Appointments'), 403);

        DB::transaction(function () use ($appointment, $nextStatus): void {
            $lockedAppointment = Appointments::query()->whereKey($appointment->getKey())->lockForUpdate()->firstOrFail();
            $allowed = match ($lockedAppointment->status) {
                'requested' => ['scheduled', 'cancelled'],
                'scheduled' => ['checked_in', 'cancelled', 'no_show'],
                'checked_in' => ['in_progress', 'cancelled'],
                'in_progress' => ['completed'],
                default => [],
            };

            if ($nextStatus === 'checked_in' && ! $lockedAppointment->date->isToday()) {
                throw ValidationException::withMessages(['status' => 'Patients can only check in on the appointment date.']);
            }

            if ($nextStatus === 'no_show') {
                $scheduledAt = CarbonImmutable::parse($lockedAppointment->date->toDateString() . ' ' . $lockedAppointment->time);

                if ($scheduledAt->isFuture()) {
                    throw ValidationException::withMessages(['status' => 'An appointment cannot be marked no-show before its scheduled time.']);
                }
            }

            if (! in_array($nextStatus, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => 'This appointment cannot move to that status.',
                ]);
            }

            if ($nextStatus === 'checked_in') {
                Doctors::query()->whereKey($lockedAppointment->doctor_id)->lockForUpdate()->firstOrFail();
                $tokensUsed = Appointments::query()
                    ->where('doctor_id', $lockedAppointment->doctor_id)
                    ->where('date', $lockedAppointment->date->toDateString())
                    ->whereNotNull('token_no')
                    ->count();
                $lockedAppointment->token_no = str_pad((string) ($tokensUsed + 1), 3, '0', STR_PAD_LEFT);
            }

            $lockedAppointment->status = $nextStatus;
            $lockedAppointment->save();
        });
    }

    /** @param array<string, mixed> $data */
    private function ensureDoctorAvailability(Doctors $doctor, array $data): void
    {
        $schedule = $doctor->availability_schedule;

        if (is_array($schedule)) {
            $schedule = collect($schedule)->mapWithKeys(fn (mixed $hours, string|int $day): array => [strtolower((string) $day) => $hours])->all();
        }

        $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        if (! is_array($schedule) || array_intersect(array_keys($schedule), $weekdays) === []) {
            return;
        }

        $start = CarbonImmutable::parse($data['date'] . ' ' . $data['time']);
        $end = $start->addMinutes((int) ($data['duration_minutes'] ?? 30));
        $windows = $schedule[strtolower($start->format('l'))] ?? [];

        if (isset($windows['start'], $windows['end'])) {
            $windows = [$windows];
        }

        $isWithinWorkingHours = collect($windows)->contains(function (mixed $window) use ($start, $end): bool {
            if (! is_array($window) || blank($window['start'] ?? null) || blank($window['end'] ?? null)) {
                return false;
            }

            try {
                $windowStart = CarbonImmutable::parse($start->toDateString() . ' ' . $window['start']);
                $windowEnd = CarbonImmutable::parse($start->toDateString() . ' ' . $window['end']);
            } catch (\Throwable) {
                return false;
            }

            return $start->greaterThanOrEqualTo($windowStart) && $end->lessThanOrEqualTo($windowEnd);
        });

        if (! $isWithinWorkingHours) {
            throw ValidationException::withMessages([
                'time' => 'The selected time is outside this doctor’s working hours.',
            ]);
        }
    }

    private function ensureDoctorCanBeBooked(Doctors $doctor): void
    {
        if ($doctor->status !== 'active') {
            throw ValidationException::withMessages([
                'doctor_id' => 'Choose an active doctor for this appointment.',
            ]);
        }
    }

    /** @param array<string, mixed> $data */
    private function ensureSlotIsAvailable(array $data, ?Appointments $ignore = null): void
    {
        $start = CarbonImmutable::parse($data['date'] . ' ' . $data['time']);

        if ($start->isPast()) {
            throw ValidationException::withMessages([
                'date' => 'Choose a date and time in the future.',
            ]);
        }

        $duration = (int) ($data['duration_minutes'] ?? 30);

        if (! in_array($duration, [15, 30, 45, 60], true)) {
            throw ValidationException::withMessages(['duration_minutes' => 'Choose a valid appointment length.']);
        }

        $end = $start->addMinutes($duration);

        if (! $end->isSameDay($start)) {
            throw ValidationException::withMessages(['time' => 'The appointment must finish before the end of the day.']);
        }
        $query = Appointments::query()
            ->where('doctor_id', $data['doctor_id'])
            ->where('date', $start->toDateString())
            ->whereIn('status', ['requested', 'scheduled', 'checked_in', 'in_progress']);

        if ($ignore) {
            $query->where('id', '!=', $ignore->getKey());
        }

        $hasConflict = $query->get(['id', 'date', 'time', 'duration_minutes'])
            ->contains(function (Appointments $existing) use ($start, $end): bool {
                $existingStart = CarbonImmutable::parse($existing->date->toDateString() . ' ' . $existing->time);
                $existingEnd = $existingStart->addMinutes((int) $existing->duration_minutes);

                return $start->lt($existingEnd) && $existingStart->lt($end);
            });

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'time' => 'This doctor already has an appointment during that time. Choose another slot.',
            ]);
        }

        $patientHasConflict = Appointments::query()
            ->where('patient_id', $data['patient_id'])
            ->where('date', $start->toDateString())
            ->whereIn('status', ['requested', 'scheduled', 'checked_in', 'in_progress'])
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->getKey()))
            ->get(['id', 'date', 'time', 'duration_minutes'])
            ->contains(function (Appointments $existing) use ($start, $end): bool {
                $existingStart = CarbonImmutable::parse($existing->date->toDateString() . ' ' . $existing->time);
                $existingEnd = $existingStart->addMinutes((int) $existing->duration_minutes);

                return $start->lt($existingEnd) && $existingStart->lt($end);
            });

        if ($patientHasConflict) {
            throw ValidationException::withMessages([
                'time' => 'This patient already has an appointment during that time. Choose another slot.',
            ]);
        }
    }
}
