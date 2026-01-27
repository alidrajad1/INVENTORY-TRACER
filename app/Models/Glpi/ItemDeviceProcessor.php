<?php

namespace App\Models\Glpi;

use Illuminate\Database\Eloquent\Model;

class ItemDeviceProcessor extends GlpiBase
{
    protected $table = 'items_deviceprocessors';

    public function deviceProcessor()
    {
        return $this->belongsTo(DeviceProcessor::class, 'deviceprocessors_id', 'id');
    }
}
