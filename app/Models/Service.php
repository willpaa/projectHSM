<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'code', 'name', 'service_type', 'department_id', 
        'price', 'insurance_coverage_percent', 'requires_pre_auth',
        'is_active', 'metadata'
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'price' => 'decimal:2',
        'insurance_coverage_percent' => 'decimal:2',
        'is_active' => 'boolean',
        'requires_pre_auth' => 'boolean'
    ];
    
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
    
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
    
    public function calculatePatientShare(Patients $patient): float
    {
        return max(0.0, round((float) $this->price - $this->calculateInsuranceShare($patient), 2));
    }
    
    public function calculateInsuranceShare(Patients $patient): float
    {
        $price = max(0.0, (float) $this->price);
        $coveragePercent = min(100.0, max(0.0, (float) $this->insurance_coverage_percent));

        if ($price <= 0 || $coveragePercent <= 0) {
            return 0;
        }

        $policy = $patient->insurance()
            ->where(fn ($query) => $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', today()))
            ->where(fn ($query) => $query->whereNull('valid_to')->orWhereDate('valid_to', '>=', today()))
            ->latest('valid_from')
            ->first();

        if (! $policy) {
            return 0;
        }

        $insuranceShare = round($price * $coveragePercent / 100, 2);
        $copay = min($price, max(0.0, (float) $policy->co_pay));
        $insuranceShare = min($insuranceShare, max(0.0, $price - $copay));

        return min($insuranceShare, $policy->remainingCoverage());
    }
    
    // Scopes
    public function scopeLabServices($query)
    {
        return $query->where('service_type', 'lab')->where('is_active', true);
    }
    
    public function scopeRadiologyServices($query)
    {
        return $query->where('service_type', 'radiology')->where('is_active', true);
    }
    
    public function scopePharmacyServices($query)
    {
        return $query->where('service_type', 'pharmacy')->where('is_active', true);
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
