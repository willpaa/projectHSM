<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyStore extends Model
{
    protected $fillable = [
        'code',
        'name',
        'location',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
