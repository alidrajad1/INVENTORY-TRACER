<?php

namespace App\Services;

use App\Models\Glpi\Computer;

class GlpiService
{
    /**
     * Cari aset di database GLPI berdasarkan Serial Number
     */
    public function searchBySerial(string $serialNumber)
    {
        // 1. Cari Data di DB GLPI
        // Pastikan Model Glpi\Computer sudah dibuat sebelumnya
        $comp = Computer::with([
            'manufacturer',
            'model',
            'cpuItems',
            'memoryItems',
            'diskItems',
            'osItems.operatingSystem'
        ])
        ->where('serial', $serialNumber)
        ->where('is_deleted', 0)
        ->first();

        if (!$comp) {
            return null;
        }

        // 2. Format Data untuk dikirim ke Controller
        return [
            'manufacturer' => $comp->manufacturer->name ?? 'Unknown',
            'model'        => $comp->model->name ?? 'Unknown',
            
            // Mapping Hardware Specs
            'cpu'     => $this->resolveCpu($comp),
            'ram'     => $this->resolveRam($comp),
            'storage' => $this->resolveStorage($comp),
            'os'      => $this->resolveOs($comp),
            
            // Tambahan
            'uuid'         => $comp->uuid,
            'otherserial'  => $comp->otherserial,
        ];
    }

    // --- HELPER METHODS ---

    private function resolveRam($comp)
    {
        $ramTotal = $comp->memoryItems->sum('size');
        return ($ramTotal > 0) ? $ramTotal . ' MB' : ($comp->memory ?? 0) . ' MB';
    }

    private function resolveCpu($comp)
    {
        if ($firstCpu = $comp->cpuItems->first()) {
            $cpuStr = $firstCpu->designation ?? 'Unknown CPU';
            if ($firstCpu->frequency > 0) {
                $cpuStr .= ' @ ' . $firstCpu->frequency . ' Mhz';
            }
            return $cpuStr;
        }
        return 'Unknown Processor';
    }

    private function resolveStorage($comp)
    {
        if ($comp->diskItems->isNotEmpty()) {
            $disks = $comp->diskItems->map(function ($disk) {
                $cap = $disk->capacity ?? 0;
                $capStr = ($cap > 1000) ? round($cap / 1024) . ' GB' : $cap . ' MB';
                return ($disk->designation ?? 'Disk') . " ($capStr)";
            })->toArray();
            return implode(', ', $disks);
        }
        return 'No Storage';
    }

    private function resolveOs($comp)
    {
        if ($osItem = $comp->osItems->first()) {
            return $osItem->operatingSystem->name ?? $osItem->name ?? 'Unknown OS';
        }
        return 'Unknown OS';
    }
}