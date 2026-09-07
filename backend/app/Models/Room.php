<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'floor_level',
        'notes',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'room_id');
    }

    public function preventiveSchedules(): HasMany
    {
        return $this->hasMany(PreventiveSchedule::class, 'room_id');
    }
}
