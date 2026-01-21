<?php

namespace App\Models\Glpi;

class Computer extends GlpiBase
{
    protected $table = 'computers';

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturers_id');
    }

    public function model()
    {
        return $this->belongsTo(ComputerModel::class, 'computermodels_id');
    }

    public function cpuItems()
    {
        return $this->hasMany(ItemDeviceProcessor::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }

    public function memoryItems()
    {
        return $this->hasMany(ItemDeviceMemory::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }

    public function diskItems()
    {
        return $this->hasMany(ItemDeviceHardDrive::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }

    public function osItems()
    {
        return $this->hasMany(ItemOperatingSystem::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }
}
