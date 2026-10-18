<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\ScopesPatientRecords;

class PatientHistory extends Model
{
    use ScopesPatientRecords;
    protected $fillable = [
        'patient_id',
        'encounter_id',
        'source',
        'activity',
        'details',
        'occurred_at',
        'created_by',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
