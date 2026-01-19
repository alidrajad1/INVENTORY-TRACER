<?php

namespace App\Models\Glpi;

use Illuminate\Database\Eloquent\Model;

class ItemOperatingSystem extends GlpiBase
{
    protected $table = 'items_operatingsystems';

    public function OperatingSystem()
    {
        return $this->belongsTo(OperatingSystem::class, 'operatingsystems_id');
    }
}
