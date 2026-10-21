<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyReportRevision extends Model
{
    protected $fillable = [
        'radiology_id',
        'revision_number',
        'revision_type',
        'reason',
        'report_data',
        'authored_by',
    ];

    protected function casts(): array
    {
        return ['report_data' => 'array'];
    }

    public function radiology(): BelongsTo
    {
        return $this->belongsTo(Radiology::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authored_by');
    }
}
