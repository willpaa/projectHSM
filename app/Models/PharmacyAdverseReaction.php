<?php

namespace App\Models;

use App\Models\Concerns\ScopesPatientRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PharmacyAdverseReaction extends Model
{
    use ScopesPatientRecords;

    protected $fillable = [
        'reaction_code',
        'patient_id',
        'pharmacy_sale_id',
        'inventory_item_id',
        'medicine_name',
        'severity',
        'reaction_description',
        'outcome',
        'status',
        'action_taken',
        'reported_by',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PharmacyAdverseReaction $reaction): void {
            if (blank($reaction->reaction_code)) {
                $reaction->reaction_code = 'ADR-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
            }
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patients::class, 'patient_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id', 'item_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
