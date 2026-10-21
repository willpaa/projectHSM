<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;

class Appointments extends Model
{
    use ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $appointment): void {
            PatientHistoryRecorder::record(
                (int) $appointment->patient_id,
                'appointment',
                'Appointment scheduled',
                implode(' | ', array_filter([
                    'Doctor ID: ' . $appointment->doctor_id,
                    'Visit type: ' . $appointment->visit_type,
                    'Date: ' . $appointment->date?->toDateString(),
                    'Time: ' . $appointment->time,
                    filled($appointment->reason) ? 'Reason: ' . $appointment->reason : null,
                ])),
                $appointment->appointment_id,
            );
        });

        static::updated(function (self $appointment): void {
            if (! $appointment->wasChanged(['status', 'date', 'time', 'duration_minutes', 'doctor_id', 'reason', 'notes'])) {
                return;
            }

            PatientHistoryRecorder::record(
                (int) $appointment->patient_id,
                'appointment',
                'Appointment updated',
                implode(' | ', array_filter([
                    'Status: ' . $appointment->status,
                    'Date: ' . $appointment->date?->toDateString(),
                    'Time: ' . $appointment->time,
                    'Duration: ' . $appointment->duration_minutes . ' minutes',
                    filled($appointment->reason) ? 'Reason: ' . $appointment->reason : null,
                    filled($appointment->notes) ? 'Notes: ' . $appointment->notes : null,
                ])),
                $appointment->appointment_id,
            );
        });
    }

    protected $attributes = [
        'duration_minutes' => 30,
        'status' => 'scheduled',
    ];

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'date',
        'time',
        'duration_minutes',
        'visit_type',
        'reason',
        'token_no',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'duration_minutes' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctors::class, 'doctor_id');
    }
}
