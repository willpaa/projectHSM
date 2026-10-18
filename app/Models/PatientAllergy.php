<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientAllergy extends Model
{
    protected $fillable = [
        'patient_id',
        'allergen',
        'reaction',
        'severity',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }
}
