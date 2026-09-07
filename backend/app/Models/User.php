<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'name',
        'username',
        'email',
        'password',
        'role',
        'asp_territory',
        'phone_number',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    const ROLE_KEPSEK = 'kepsek';
    const ROLE_DEPT_YGPKB = 'dept_ygpkb';
    const ROLE_ASP_WILAYAH = 'asp_wilayah';
    const ROLE_KD_SARPRAS = 'kd_sarpras';
    const ROLE_ADMIN_SARPRAS = 'admin_sarpras';
    const ROLE_KOORDINATOR_SKI = 'koordinator_ski';
    const ROLE_KOORDINATOR_UTAMA = 'koordinator_utama';

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
