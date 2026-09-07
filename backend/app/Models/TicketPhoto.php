<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'photo_type',
        'file_path',
        'file_size_kb',
        'caption',
    ];

    const UPDATED_AT = null; // No updated_at in schema

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
