<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    //
    use HasFactory, LogsActivity;
    protected static $recordEvents = ['created', 'updated', 'deleted'];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()
            ->logAll()
            ->useLogName('Company');
    }
    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'phone',
        'email',
        'website',
        'gstin',
        'pan',
        'cin',
        'logo',
        'currency',
        'currency_symbol',
    ];
    protected $casts = [
        'status' => 'boolean',
    ];
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
