<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;

class IPD extends Model
{
    use ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $ipd): void {
            $ipd->syncBedAssignment();
            PatientHistoryRecorder::record(
                $ipd->patient_id ? (int) $ipd->patient_id : null,
                'ipd',
                'IPD admission created',
                implode(' | ', array_filter([
                    'Status: ' . $ipd->status,
                    filled($ipd->diagnosis) ? 'Diagnosis: ' . $ipd->diagnosis : null,
                    filled($ipd->department) ? 'Department: ' . $ipd->department : null,
                    $ipd->doctor_id ? 'Doctor ID: ' . $ipd->doctor_id : null,
                ])),
                $ipd->encounter_id,
            );
        });

        static::updated(function (self $ipd): void {
            if ($ipd->wasChanged(['bed_id', 'status', 'patient_id', 'discharge_date'])) {
                $ipd->syncBedAssignment($ipd->getOriginal('bed_id'));
            }

            if ($ipd->wasChanged(['status', 'diagnosis', 'treatment_plan', 'discharge_date', 'bed_id'])) {
                PatientHistoryRecorder::record(
                    $ipd->patient_id ? (int) $ipd->patient_id : null,
                    'ipd',
                    $ipd->status === 'discharged' ? 'IPD patient discharged' : 'IPD encounter updated',
                    implode(' | ', array_filter([
                        'Status: ' . $ipd->status,
                        filled($ipd->diagnosis) ? 'Diagnosis: ' . $ipd->diagnosis : null,
                        filled($ipd->treatment_plan) ? 'Treatment plan: ' . $ipd->treatment_plan : null,
                        $ipd->discharge_date ? 'Discharge date: ' . $ipd->discharge_date->toDateString() : null,
                        filled($ipd->bed_id) ? 'Bed: ' . $ipd->bed_id : null,
                    ])),
                    $ipd->encounter_id,
                );
            }
        });

        static::deleted(function (self $ipd): void {
            $ipd->releaseBedAssignment($ipd->bed_id);
        });
    }

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'type',
        'doctor_id',
        'department',
        'diagnosis',
        'admission_date',
        'discharge_date',
        'bed_id',
        'treatment_plan',
        'prescription_id',
        'status',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'discharge_date' => 'date',
    ];

    public function syncBedAssignment(?string $previousBedId = null): void
    {
        if ($previousBedId && $previousBedId !== $this->bed_id) {
            $this->releaseBedAssignment($previousBedId);
        }

        if (blank($this->bed_id)) {
            return;
        }

        $isAvailable = in_array($this->status, ['discharged', 'transferred'], true)
            || filled($this->discharge_date);

        Beds::where('bed_id', $this->bed_id)->update([
            'current_patient_id' => $isAvailable ? null : $this->patient_id,
            'occupancy_status' => $isAvailable ? 'available' : 'occupied',
        ]);
    }

    public function releaseBedAssignment(?string $bedId = null): void
    {
        $bedId = $bedId ?? $this->bed_id;

        if (blank($bedId)) {
            return;
        }

        Beds::where('bed_id', $bedId)->update([
            'current_patient_id' => null,
            'occupancy_status' => 'available',
        ]);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctors::class, 'doctor_id');
    }


    public function bed(): BelongsTo
    {
        return $this->belongsTo(Beds::class, 'bed_id', 'bed_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'prescription_id');
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class, 'encounter_id', 'encounter_id');
    }

    public function triage(): HasOne
    {
        return $this->hasOne(Triage::class, 'encounter_id', 'encounter_id');
    }
}
