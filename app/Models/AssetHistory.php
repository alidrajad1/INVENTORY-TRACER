<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class AssetHistory extends Model
{
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'asset_id',
        'user_id',
        'employee_id',
        'action',
        'status_before',
        'status_after',
        'condition',
        'location_id',
        'notes',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
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
