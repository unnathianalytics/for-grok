<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Account extends Model
{
    use HasFactory, LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()
            ->logAll()
            ->useLogName('Account');
    }

    protected $fillable = [
        'group_id',
        'name',
        'address',
        'mobile',
        'email',
        'is_registered',
        'gstin',
        'cr_dr',
        'op_balance',
        'is_editable',
        'is_deletable',
        'image',
        'user',
    ];
    protected $casts = [
        'is_registered' => 'boolean',
        'is_editable'  => 'boolean',
        'is_deletable' => 'boolean',
        'image'        => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AccountGroup::class, 'group_id');
    }

    public function scopeInvoiceAccounts()
    {
        return $this->whereIn('group_id', [12, 16, 20, 31]);
    }
}
