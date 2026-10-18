<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;

class Laboratory extends Model
{
    use ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $lab): void {
            PatientHistoryRecorder::record(
                $lab->patient_id ? (int) $lab->patient_id : null,
                'laboratory',
                'Laboratory test ordered',
                implode(' | ', array_filter([
                    'Test: ' . $lab->test_name,
                    'Status: ' . $lab->status,
                    filled($lab->test_type) ? 'Type: ' . $lab->test_type : null,
                ])),
                $lab->encounter_id ?: $lab->lab_id,
            );
        });

        static::updated(function (self $lab): void {
            if (! $lab->wasChanged([
                'status',
                'result_status',
                'result_value',
                'result_data',
                'result_date',
                'notes',
            ])) {
                return;
            }

            PatientHistoryRecorder::record(
                $lab->patient_id ? (int) $lab->patient_id : null,
                'laboratory',
                'Laboratory result updated',
                implode(' | ', array_filter([
                    'Test: ' . $lab->test_name,
                    'Status: ' . $lab->status,
                    'Result status: ' . $lab->result_status,
                    filled($lab->result_value) ? 'Result: ' . $lab->result_value : null,
                    filled($lab->result_data) ? 'Structured result: ' . json_encode($lab->result_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                    filled($lab->notes) ? 'Notes: ' . $lab->notes : null,
                ])),
                $lab->encounter_id ?: $lab->lab_id,
            );
        });
    }

    protected $fillable = [
        'lab_id',
        'patient_id',
        'doctor_id',
        'laboratorist_id',
        'service_request_id',
        'encounter_type',
        'encounter_id',
        'test_name',
        'test_type',
        'sample_type',
        'test_date',
        'result_date',
        'result_value',
        'result_data',
        'result_status',
        'normal_range',
        'cost',
        'notes',
        'report_path',
        'lab_report_path',
        'status',
    ];

    protected $casts = [
        'test_date' => 'date',
        'result_date' => 'date',
        'cost' => 'decimal:2',
        'result_data' => 'array',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctors::class, 'doctor_id');
    }

    public function laboratorist(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'laboratorist_id');
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
