<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;

class OPD extends Model
{
    use ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $opd): void {
            $opd->syncBedAssignment();
            PatientHistoryRecorder::record(
                $opd->patient_id ? (int) $opd->patient_id : null,
                'opd',
                'OPD encounter opened',
                implode(' | ', array_filter([
                    filled($opd->diagnosis) ? 'Diagnosis: ' . $opd->diagnosis : null,
                    filled($opd->department) ? 'Department: ' . $opd->department : null,
                    $opd->doctor_id ? 'Doctor ID: ' . $opd->doctor_id : null,
                ])),
                $opd->encounter_id,
            );
        });

        static::updated(function (self $opd): void {
            if ($opd->wasChanged(['bed_id', 'status', 'patient_id', 'discharge_date'])) {
                $opd->syncBedAssignment($opd->getOriginal('bed_id'));
            }

            if ($opd->wasChanged([
                'status',
                'diagnosis',
                'icd10_code',
                'treatment_plan',
                'discharge_date',
                'discharge_summary',
                'follow_up_date',
                'bed_id',
            ])) {
                PatientHistoryRecorder::record(
                    $opd->patient_id ? (int) $opd->patient_id : null,
                    'opd',
                    $opd->status === 'closed' ? 'OPD encounter discharged' : 'OPD encounter updated',
                    implode(' | ', array_filter([
                        'Status: ' . $opd->status,
                        filled($opd->diagnosis) ? 'Diagnosis: ' . $opd->diagnosis : null,
                        filled($opd->treatment_plan) ? 'Treatment plan: ' . $opd->treatment_plan : null,
                        filled($opd->discharge_summary) ? 'Discharge summary: ' . $opd->discharge_summary : null,
                        $opd->follow_up_date ? 'Follow-up: ' . $opd->follow_up_date->toDateString() : null,
                    ])),
                    $opd->encounter_id,
                );
            }
        });

        static::deleted(function (self $opd): void {
            $opd->releaseBedAssignment($opd->bed_id);
        });
    }

    protected $fillable = [
        'encounter_id',
        'patient_id',
        'type',
        'doctor_id',
        'department',
        'diagnosis',
        'icd10_code',
        'admission_date',
        'discharge_date',
        'follow_up_date',
        'bed_id',
        'treatment_plan',
        'discharge_summary',
        'prescription_id',
        'status',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'discharge_date' => 'date',
        'follow_up_date' => 'date',
    ];

    public function syncBedAssignment(?string $previousBedId = null): void
    {
        if ($previousBedId && $previousBedId !== $this->bed_id) {
            $this->releaseBedAssignment($previousBedId);
        }

        if (blank($this->bed_id)) {
            return;
        }

        $isAvailable = in_array($this->status, ['discharged', 'closed', 'transferred'], true)
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    
}
