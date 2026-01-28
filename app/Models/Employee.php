<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Employee extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = ["id"];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    function histories(): HasMany
    {
        return $this->hasMany(AssetHistory::class);
    }

    function loanRequests(): HasMany
    {
        return $this->hasMany(LoanRequest::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Catat semua field
            ->logOnlyDirty() // Hanya catat yang berubah saja
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Abaikan timestamp
            ->useLogName('asset'); // Label log
    }
}
