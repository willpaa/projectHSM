<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyBatch extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'batch_no',
        'expiry_date',
        'quantity_received',
        'quantity_remaining',
        'purchase_price',
        'supplier_id',
        'reference',
        'received_at',
        'received_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'received_at' => 'datetime',
        'purchase_price' => 'decimal:2',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'inventory_item_id', 'item_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(PharmacyBatchMovement::class);
    }
}
