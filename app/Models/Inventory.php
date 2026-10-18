<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventory extends Model
{
    protected $table = 'inventories';
    protected $primaryKey = 'item_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'item_id',
        'sku_code',
        'barcode',
        'item_name',
        'generic_name',
        'atc_code',
        'drug_form',
        'strength',
        'therapeutic_class',
        'requires_prescription',
        'is_controlled',
        'category',
        'unit',
        'pack_size',
        'quantity',
        'reorder_level',
        'supplier_id',
        'purchase_price',
        'selling_price',
        'tax_rate',
        'expiry_date',
        'store_location',
        'storage_condition',
        'is_active',
        'supplier_ref_id',
        'stock_movement_log',
    ];

    protected $casts = [
        'requires_prescription' => 'boolean',
        'is_controlled' => 'boolean',
        'is_active' => 'boolean',
        'pack_size' => 'integer',
        'tax_rate' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_ref_id');
    }

    /** Net selling price including tax, per unit. */
    public function sellingPriceWithTax(): float
    {
        return (float) $this->selling_price * (1 + ((float) $this->tax_rate / 100));
    }

    /** Stock status used by the table badge and low-stock filters. */
    public function stockStatus(): string
    {
        $qty = $this->batches()->where('quantity_remaining', '>', 0)->sum('quantity_remaining') ?: (int) $this->quantity;

        return match (true) {
            $qty <= 0 => 'out_of_stock',
            $qty <= $this->reorder_level => 'low_stock',
            default => 'in_stock',
        };
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_id', 'item_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(PharmacyBatch::class, 'inventory_item_id', 'item_id');
    }

    /**
     * Whether this item is a controlled substance (narcotics/psychotropics).
     */
    public function isControlled(): bool
    {
        return (bool) $this->is_controlled;
    }

    /**
     * Whether this item must be kept under cold-chain conditions.
     */
    public function requiresColdChain(): bool
    {
        return in_array($this->storage_condition, ['cold', 'frozen'], true);
    }

    /**
     * Whether the raw stock level has fallen to or below the reorder level.
     */
    public function isLowStock(): bool
    {
        return (int) $this->quantity <= (int) $this->reorder_level;
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity', '<=', 'reorder_level');
    }

    public function scopeControlled($query)
    {
        return $query->where('is_controlled', true);
    }
}
