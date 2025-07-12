<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialNumber extends Model
{
    protected $fillable = ['item_id', 'serial_number', 'description', 'invoice_item_id', 'is_opening_stock'];
    protected $casts = [
        'is_opening_stock'
    ];
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }
}
