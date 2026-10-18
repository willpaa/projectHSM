<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'order_date',
        'expected_date',
        'status',
        'total_amount',
        'notes',
        'created_by',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'total_amount' => 'decimal:2',
        'received_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isReceivable(): bool
    {
        return in_array($this->status, ['ordered', 'partial'], true);
    }

    public function isFullyReceived(): bool
    {
        return $this->items->isNotEmpty()
            && $this->items->every(fn (PurchaseOrderItem $item): bool => $item->outstanding() === 0);
    }
}
