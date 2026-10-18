<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;

class Triage extends Model
{
    use ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $triage): void {
            PatientHistoryRecorder::record(
                (int) $triage->patient_id,
                'triage',
                'Patient triaged',
                implode(' | ', array_filter([
                    'Priority: ' . $triage->priority,
                    filled($triage->chief_complaint) ? 'Complaint: ' . $triage->chief_complaint : null,
                    filled($triage->temperature) ? 'Temperature: ' . $triage->temperature : null,
                    filled($triage->blood_pressure) ? 'Blood pressure: ' . $triage->blood_pressure : null,
                    filled($triage->pulse_rate) ? 'Pulse: ' . $triage->pulse_rate : null,
                    filled($triage->respiratory_rate) ? 'Respiratory rate: ' . $triage->respiratory_rate : null,
                    filled($triage->oxygen_saturation) ? 'Oxygen saturation: ' . $triage->oxygen_saturation : null,
                    filled($triage->notes) ? 'Notes: ' . $triage->notes : null,
                ])),
                $triage->encounter_id ?: $triage->triage_id,
            );
        });

        static::updated(function (self $triage): void {
            if (! $triage->wasChanged([
                'priority',
                'chief_complaint',
                'temperature',
                'blood_pressure',
                'pulse_rate',
                'respiratory_rate',
                'oxygen_saturation',
                'notes',
                'status',
            ])) {
                return;
            }

            PatientHistoryRecorder::record(
                (int) $triage->patient_id,
                'triage',
                'Triage updated',
                implode(' | ', array_filter([
                    'Status: ' . $triage->status,
                    'Priority: ' . $triage->priority,
                    filled($triage->chief_complaint) ? 'Complaint: ' . $triage->chief_complaint : null,
                    filled($triage->temperature) ? 'Temperature: ' . $triage->temperature : null,
                    filled($triage->blood_pressure) ? 'Blood pressure: ' . $triage->blood_pressure : null,
                    filled($triage->pulse_rate) ? 'Pulse: ' . $triage->pulse_rate : null,
                    filled($triage->respiratory_rate) ? 'Respiratory rate: ' . $triage->respiratory_rate : null,
                    filled($triage->oxygen_saturation) ? 'Oxygen saturation: ' . $triage->oxygen_saturation : null,
                    filled($triage->notes) ? 'Notes: ' . $triage->notes : null,
                ])),
                $triage->encounter_id ?: $triage->triage_id,
            );
        });
    }

    protected $fillable = [
        'triage_id',
        'patient_id',
        'nurse_id',
        'encounter_id',
        'priority',
        'chief_complaint',
        'temperature',
        'blood_pressure',
        'pulse_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'notes',
        'status',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }

    public function opdEncounter(): BelongsTo
    {
        return $this->belongsTo(OPD::class, 'encounter_id', 'encounter_id');
    }

    public function ipdEncounter(): BelongsTo
    {
        return $this->belongsTo(IPD::class, 'encounter_id', 'encounter_id');
    }
}
