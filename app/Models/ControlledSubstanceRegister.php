<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ControlledSubstanceRegister extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'batch_no',
        'transaction_type',
        'quantity',
        'running_balance',
        'patient_id',
        'prescription_id',
        'pharmacist_id',
        'witness',
        'notes',
        'occurred_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'running_balance' => 'integer',
        'occurred_at' => 'datetime',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id', 'item_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }
}
