<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'unit_id', 'vendor_id', 'year', 'sequence_number',
        'po_number', 'vendor_name_snapshot', 'vendor_pic_snapshot',
        'vendor_phone_snapshot', 'vendor_address_snapshot',
        'is_incidental_vendor', 'is_new_vendor_proposal', 'proposed_vendor_specialty',
        'subtotal', 'discount_amount', 'grand_total', 'terbilang_text',
        'po_status', 'scan_document_path', 'created_by', 'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_incidental_vendor' => 'boolean',
        'is_new_vendor_proposal' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    
    public function items(): HasMany { return $this->hasMany(PoItem::class, 'purchase_order_id'); }
}
