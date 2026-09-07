<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'inventory_code',
        'item_description',
        'quantity',
        'unit_metric',
        'condition_status',
        'estimated_unit_price',
        'estimated_subtotal',
        'item_notes',
    ];

    protected $casts = [
        'estimated_unit_price' => 'decimal:2',
        'estimated_subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
