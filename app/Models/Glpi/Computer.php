<?php

namespace App\Models\Glpi;

class Computer extends GlpiBase
{
    protected $table = 'computers';

    // Relasi ke Merk
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturers_id');
    }

    // Relasi ke Model
    public function model()
    {
        return $this->belongsTo(ComputerModel::class, 'computermodels_id');
    }

    // Relasi ke Processor (Item Linked)
    public function cpuItems()
    {
        return $this->hasMany(ItemDeviceProcessor::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }

    // Relasi ke RAM (Item Linked)
    public function memoryItems()
    {
        return $this->hasMany(ItemDeviceMemory::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }

    // Relasi ke Storage (Item Linked)
    public function diskItems()
    {
        return $this->hasMany(ItemDeviceHardDrive::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }

    // Relasi ke OS Detail
    public function osItems()
    {
        return $this->hasMany(ItemOperatingSystem::class, 'items_id')
                    ->where('itemtype', 'Computer');
    }
}
