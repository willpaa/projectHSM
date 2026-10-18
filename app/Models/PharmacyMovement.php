<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;

class PharmacyMovement extends Model
{
    use ScopesPatientRecords;
    protected $fillable = [
        'pharmacy_id',
        'movement_type',
        'direction',
        'quantity',
        'patient_id',
        'prescription_id',
        'reference',
        'notes',
        'created_by',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
