<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'supplier_code',
        'tin_number',
        'license_number',
        'license_expires_at',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'country',
        'notes',
        'status',
        'payment_terms',
        'credit_limit',
    ];

    protected $casts = [
        'license_expires_at' => 'date',
        'credit_limit' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Supplier $supplier): void {
            if (blank($supplier->supplier_code)) {
                $supplier->supplier_code = static::nextCode();
            }
        });
    }

    public static function nextCode(): string
    {
        $last = static::query()->where('supplier_code', 'like', 'SUP-%')->orderByDesc('supplier_code')->value('supplier_code');
        $number = $last ? ((int) substr($last, 4)) + 1 : 1;

        return 'SUP-' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id');
    }
}
