<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacySaleBatch extends Model
{
    protected $fillable = [
        'pharmacy_sale_id',
        'pharmacy_batch_id',
        'quantity',
        'unit_cost',
        'cost_amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'cost_amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(PharmacySale::class, 'pharmacy_sale_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PharmacyBatch::class, 'pharmacy_batch_id');
    }
}
