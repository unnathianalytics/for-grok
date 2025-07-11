<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AccountingVoucher extends Model
{
    use HasFactory, LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()
            ->logAll()
            ->useLogName('AccountVoucher');
    }
    protected $fillable = [
        'voucher_series_id',
        'transaction_date',
        'transaction_time',
        'voucher_number',
        'accounting_type_id',
        'voucher_notes',
        'user',
    ];
    public function accountingType(): BelongsTo
    {
        return $this->belongsTo(AccountingType::class);
    }
    public function accountingVoucherItems()
    {
        return $this->hasMany(AccountingVoucherItem::class, 'accounting_voucher_id');
    }
}
