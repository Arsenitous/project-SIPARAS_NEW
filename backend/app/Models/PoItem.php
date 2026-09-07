<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'item_description',
        'order_quantity',
        'unit_metric',
        'unit_price',
        'item_subtotal',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'item_subtotal' => 'decimal:2',
        'order_quantity' => 'integer',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}
