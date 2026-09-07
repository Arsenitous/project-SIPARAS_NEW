<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSignatory extends Model
{
    protected $fillable = [
        'document_type',
        'column_position',
        'signature_label',
        'default_signer_name',
        'default_signer_role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
