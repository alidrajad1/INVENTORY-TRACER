<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Category extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'name',
        'code',
    ];

    function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
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
