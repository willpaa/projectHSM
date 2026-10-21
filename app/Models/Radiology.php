<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
                    filled($radiology->clinical_indication) ? 'Indication: ' . $radiology->clinical_indication : null,
                    filled($radiology->clinical_question) ? 'Clinical question: ' . $radiology->clinical_question : null,
                    'Urgency: ' . $radiology->urgency,
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
                'report_technique',
                'comparison_studies',
                'findings',
                'conclusion',
                'completed_at',
                'critical_contact_name',
                'critical_contact_method',
                'critical_contacted_at',
                'exam_name',
                'modality',
                'body_part',
                'laterality',
                'clinical_indication',
                'clinical_history',
                'clinical_question',
                'urgency',
                'pregnancy_status',
                'contrast_plan',
                'contrast_safety_notes',
                'mri_safety_notes',
            ])) {
                return;
            }

            PatientHistoryRecorder::record(
                $radiology->patient_id ? (int) $radiology->patient_id : null,
                'radiology',
                'Radiology result updated',
                implode(' | ', array_filter([
                    'Exam: ' . $radiology->exam_name,
                    'Modality: ' . $radiology->modality,
                    'Indication: ' . $radiology->clinical_indication,
                    'Clinical question: ' . $radiology->clinical_question,
                    'Status: ' . $radiology->status,
                    'Result status: ' . $radiology->result_status,
                    filled($radiology->result_summary) ? 'Summary: ' . $radiology->result_summary : null,
                    filled($radiology->report_technique) ? 'Technique: ' . $radiology->report_technique : null,
                    filled($radiology->comparison_studies) ? 'Comparison: ' . $radiology->comparison_studies : null,
                    filled($radiology->findings) ? 'Findings: ' . $radiology->findings : null,
                    filled($radiology->conclusion) ? 'Conclusion: ' . $radiology->conclusion : null,
                    filled($radiology->critical_contact_name) ? 'Critical result communicated to: ' . $radiology->critical_contact_name : null,
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
        'body_part',
        'laterality',
        'clinical_indication',
        'clinical_history',
        'clinical_question',
        'urgency',
        'pregnancy_status',
        'contrast_plan',
        'contrast_safety_notes',
        'mri_safety_notes',
        'exam_date',
        'result_summary',
        'findings',
        'conclusion',
        'report_technique',
        'comparison_studies',
        'report_image',
        'critical_contact_name',
        'critical_contact_method',
        'critical_contacted_at',
        'reviewed_by',
        'reviewed_at',
        'doctor_review_notes',
        'completed_at',
        'result_status',
        'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'completed_at' => 'datetime',
        'critical_contacted_at' => 'datetime',
        'reviewed_at' => 'datetime',
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

    public function reportRevisions(): HasMany
    {
        return $this->hasMany(RadiologyReportRevision::class)->orderBy('revision_number');
    }

    public function reviewingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function paymentCleared(): bool
    {
        return $this->serviceRequest?->canProceedForService() ?? false;
    }
}
