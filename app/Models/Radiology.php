<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;

class Radiology extends Model
{
    use ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $radiology): void {
            PatientHistoryRecorder::record(
                $radiology->patient_id ? (int) $radiology->patient_id : null,
                'radiology',
                'Radiology exam ordered',
                implode(' | ', array_filter([
                    'Exam: ' . $radiology->exam_name,
                    'Modality: ' . $radiology->modality,
                    'Status: ' . $radiology->status,
                ])),
                $radiology->encounter_id ?: $radiology->radiology_id,
            );
        });

        static::updated(function (self $radiology): void {
            if (! $radiology->wasChanged([
                'status',
                'result_status',
                'result_summary',
                'findings',
                'conclusion',
                'completed_at',
            ])) {
                return;
            }

            PatientHistoryRecorder::record(
                $radiology->patient_id ? (int) $radiology->patient_id : null,
                'radiology',
                'Radiology result updated',
                implode(' | ', array_filter([
                    'Exam: ' . $radiology->exam_name,
                    'Status: ' . $radiology->status,
                    'Result status: ' . $radiology->result_status,
                    filled($radiology->result_summary) ? 'Summary: ' . $radiology->result_summary : null,
                    filled($radiology->findings) ? 'Findings: ' . $radiology->findings : null,
                    filled($radiology->conclusion) ? 'Conclusion: ' . $radiology->conclusion : null,
                ])),
                $radiology->encounter_id ?: $radiology->radiology_id,
            );
        });
    }

    protected $fillable = [
        'radiology_id',
        'patient_id',
        'doctor_id',
        'radiologist_id',
        'service_request_id',
        'encounter_type',
        'encounter_id',
        'exam_name',
        'modality',
        'exam_date',
        'result_summary',
        'findings',
        'conclusion',
        'report_image',
        'completed_at',
        'result_status',
        'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctors::class, 'doctor_id');
    }

    public function radiologist(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'radiologist_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function paymentCleared(): bool
    {
        return $this->serviceRequest?->canProceedForService() ?? false;
    }
}
