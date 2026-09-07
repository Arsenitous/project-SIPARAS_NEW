<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BastDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'bast_type',
        'handover_date',
        'executor_or_vendor_name',
        'actual_cost',
        'general_notes',
        'scan_document_path',
        'status',
        'created_by',
    ];

    protected $casts = [
        'handover_date' => 'date',
        'actual_cost' => 'decimal:2',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
