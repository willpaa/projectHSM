<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStorageTemperature extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'storage_location',
        'temperature_celsius',
        'humidity',
        'within_range',
        'notes',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'temperature_celsius' => 'decimal:2',
        'humidity' => 'decimal:2',
        'within_range' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id', 'item_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
