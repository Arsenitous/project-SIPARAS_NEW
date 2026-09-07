<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreventiveSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'location_name',
        'facility_name',
        'inventory_code',
        'frequency_months',
        'last_service_date',
        'default_vendor_id',
        'is_active',
    ];

    protected $casts = [
        'frequency_months' => 'integer',
        'last_service_date' => 'date',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function defaultVendor(): BelongsTo { return $this->belongsTo(Vendor::class, 'default_vendor_id'); }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
