<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyBatchMovement extends Model
{
    protected $fillable = [
        'pharmacy_batch_id',
        'operation_type',
        'quantity_change',
        'reference',
        'notes',
        'user_id',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PharmacyBatch::class, 'pharmacy_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
