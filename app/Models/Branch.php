<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Branch extends Model
{
    //
    use HasFactory, LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()
            ->logAll()
            ->useLogName('Branch');
    }
    protected $fillable = [
        'name',
        'company_id',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'phone',
        'email',
        'website',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function material_centers(): HasMany
    {
        return $this->hasMany(MaterialCenter::class);
    }

    public function voucherSeries(): HasMany
    {
        return $this->hasMany(VoucherSeries::class);
    }
}
