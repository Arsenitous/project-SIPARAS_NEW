<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id', 'location_name', 'user_id', 'preventive_schedule_id',
        'procedure_type', 'routing_path', 'target_object_category',
        'specific_location', 'item_name', 'inventory_code',
        'target_unit_id', 'target_location_name', 'disposal_action',
        'disposal_destination_or_notes', 'estimated_salvage_value',
        'title', 'urgency_level', 'problem_cause', 'action_plan',
        'estimated_cost', 'asp_approval_status', 'asp_approval_by',
        'asp_approval_at', 'asp_notes', 'kd_approval_status',
        'kd_approval_by', 'kd_approval_at', 'kd_notes', 'status',
        'actual_cost', 'completed_at', 'completed_by',
    ];

    protected $casts = [
        'estimated_salvage_value' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'asp_approval_at' => 'datetime',
        'kd_approval_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function unit(): BelongsTo { return $this->belongsTo(Unit::class, 'unit_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function preventiveSchedule(): BelongsTo { return $this->belongsTo(PreventiveSchedule::class, 'preventive_schedule_id'); }
    public function targetUnit(): BelongsTo { return $this->belongsTo(Unit::class, 'target_unit_id'); }
    public function aspApprovalBy(): BelongsTo { return $this->belongsTo(User::class, 'asp_approval_by'); }
    public function kdApprovalBy(): BelongsTo { return $this->belongsTo(User::class, 'kd_approval_by'); }
    public function completedBy(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }
    
    public function items(): HasMany { return $this->hasMany(TicketItem::class, 'ticket_id'); }
    public function photos(): HasMany { return $this->hasMany(TicketPhoto::class, 'ticket_id'); }
    
    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class, 'ticket_id'); }
    public function bastDocument(): HasOne { return $this->hasOne(BastDocument::class, 'ticket_id'); }
}
