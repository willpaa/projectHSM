<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;
use App\Services\PatientHistoryRecorder;


class Payments extends Model
{
    use HasFactory, ScopesPatientRecords;

    protected static function booted(): void
    {
        static::created(function (self $payment): void {
            PatientHistoryRecorder::record(
                $payment->patient_id ? (int) $payment->patient_id : null,
                'payment',
                'Payment recorded',
                implode(' | ', array_filter([
                    'Invoice: ' . $payment->invoice_number,
                    'Amount: ' . $payment->amount,
                    'Method: ' . ($payment->payment_mode ?: $payment->payment_method),
                    'Status: ' . $payment->payment_status,
                ])),
                $payment->serviceRequest?->encounter_id ?: $payment->invoice_number,
                $payment->collected_by,
            );
        });
    }

    /**
     * Explicitly defining the table name to ensure it matches your database.
     * @var string
     */
    protected $table = 'payments';
    protected $fillable = [
        'invoice_number',
        'patient_id',
        'service_request_id',
        'collected_by',
        'payment_mode',
        'amount',
        'split_details',
        'payment_method',
        'payment_status',
        'payment_date',
        'transaction_id',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'split_details' => 'array',
    ];
   
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
