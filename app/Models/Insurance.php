<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;
use Illuminate\Support\Carbon;

class Insurance extends Model
{
    use ScopesPatientRecords;
    protected $fillable = [
        'policy_id',
        'patient_id',
        'provider_name',
        'policy_no',
        'coverage_limit',
        'co_pay',
        'valid_from',
        'valid_to',
        'claim_id',
        'claim_status',
        'approved_amount',
    ];

    protected $casts = [
        'coverage_limit' => 'decimal:2',
        'co_pay' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'approved_amount' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function isValidOn(?Carbon $date = null): bool
    {
        $date ??= today();

        return (! $this->valid_from || $this->valid_from->lte($date))
            && (! $this->valid_to || $this->valid_to->gte($date));
    }

    public function remainingCoverage(): float
    {
        $limit = (float) $this->coverage_limit;

        if ($limit <= 0) {
            return 0.0;
        }

        // Insurance payments represent claim amounts recorded as settled in this HMS.
        $payments = Payments::query()
            ->where('patient_id', $this->patient_id)
            ->where('payment_mode', 'insurance');

        if ($this->valid_from) {
            $payments->whereDate('payment_date', '>=', $this->valid_from);
        }

        if ($this->valid_to) {
            $payments->whereDate('payment_date', '<=', $this->valid_to);
        }

        $used = $payments->sum('amount');

        return max(0.0, round($limit - (float) $used, 2));
    }
}
