<?php

namespace App\Models\Glpi;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class GlpiBase extends Model
{
    use LogsActivity;
    protected $connection = 'glpi';
    public $timestamps = false;
    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Catat semua field
            ->logOnlyDirty() // Hanya catat yang berubah saja
            ->dontLogIfAttributesChangedOnly(['updated_at']) // Abaikan timestamp
            ->useLogName('asset'); // Label log
    }
}
