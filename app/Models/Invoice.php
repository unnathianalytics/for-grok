<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Query\Builder;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()
            ->logAll()
            ->useLogName('Invoice');
    }
    protected $fillable = [
        'voucher_series_id',
        'invoice_type_id',
        'tax_type_id',
        'invoice_number',
        'invoice_date',
        'invoice_time',
        'account_id',
        'einvoice_ack_date',
        'einvoice_ack_no',
        'einvoice_irn',
        'einvoice_qrcode',
        'einvoice_qrcode_ksa',
        'einvoice_required',
        'eway_bill_date_gst',
        'eway_bill_no_gst',
        'eway_bill_required',
        'description',
        'user',
    ];

    public function invoiceType()
    {
        return $this->belongsTo(InvoiceType::class, 'invoice_type_id');
    }
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    public function invoiceSundries()
    {
        return $this->hasMany(InvoiceSundry::class);
    }
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
    public function series()
    {
        return $this->belongsTo(VoucherSeries::class, 'voucher_series_id');
    }
    public function taxType(): BelongsTo
    {
        return $this->belongsTo(TaxType::class, 'tax_type_id');
    }

    public static function invoiceItemTotal(int $invoiceId): float
    {
        return InvoiceItem::where('invoice_id', $invoiceId)
            ->sum('item_amount');
    }
    public static function invoiceSundryTotal(int $invoiceId): float
    {
        $is = InvoiceSundry::where('invoice_id', $invoiceId)->get();
        $sum = 0;
        foreach ($is as $s) {
            $tempAmount = (float) ($s['sundry_amount'] ?? 0);
            $adjustment = $s['amount_adjustment'] === '-' ? -1 : 1;

            $sum += $adjustment * $tempAmount;
        }
        return $sum;
    }
    public function getInvoiceTotal(int $invoiceId)
    {
        return $this->invoiceItemTotal($invoiceId,) + $this->invoiceSundryTotal($invoiceId);
    }
}
