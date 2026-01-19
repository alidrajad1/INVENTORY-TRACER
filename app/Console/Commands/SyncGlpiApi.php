<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{Asset, Category, Location};
use App\Models\Glpi\Computer as GlpiComputer;

class SyncGlpiApi extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'glpi:sync-db';

    /**
     * The console command description.
     */
    protected $description = 'Sync assets directly from GLPI Database using Eloquent relationships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Starting GLPI Database Sync ---');
        $this->info('Connecting to GLPI Database...');

        // 1. Fetch Data with Eager Loading
        // We load all related tables in a single query to prevent the N+1 query problem.
        // This is significantly faster than looping through API requests.
        $glpiComputers = GlpiComputer::with([
            'manufacturer',
            'model',
            'cpuItems',
            'memoryItems',
            'diskItems',
            'osItems.operatingSystem'
        ])
            ->where('is_deleted', 0) // Process only active computers
            ->get();

        $total = $glpiComputers->count();
        $this->info("Found {$total} computers. Processing...");

        // 2. Prepare Default Data
        // These IDs are used only when creating new assets to prevent foreign key errors.
        $defaultCatId = Category::firstOrCreate(['name' => 'Uncategorized'], ['code' => 'UNCAT'])->id;
        $defaultLocId = Location::firstOrCreate(['name' => 'Unknown Location'], ['building' => 'N/A'])->id;

        // 3. Initialize Progress Bar
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($glpiComputers as $comp) {
            // Skip invalid entries without a serial number
            if (empty($comp->serial)) {
                $bar->advance();
                continue;
            }

            // --- A. Resolve Attributes ---
            $brand = $comp->manufacturer->name ?? 'Unknown';
            $model = $comp->model->name ?? 'Unknown';
            $specs = $this->resolveHardwareSpecs($comp);
            $assetTag = $this->resolveAssetTag($comp->otherserial);

            // --- B. Sync Logic (FirstOrNew) ---
            // We use firstOrNew to find the asset. We do NOT save immediately.
            $asset = Asset::firstOrNew(['serial_number' => $comp->serial]);

            // Always update technical data from GLPI (Single Source of Truth)
            $asset->name = $comp->name;
            $asset->brand = $brand;
            $asset->model = $model;
            $asset->hardware_specs = $specs;
            $asset->last_seen_at = $comp->date_mod ?? now();

            // Update Asset Tag only if GLPI has a value
            if ($assetTag) {
                $asset->asset_tag = $assetTag;
            }

            // --- C. Admin Protection Logic ---
            // We only set Category and Location for NEW assets.
            // If the asset exists, we respect the manual changes made by the Admin in Laravel.
            if (!$asset->exists) {
                $asset->category_id = $defaultCatId;
                $asset->location_id = $defaultLocId;

                // Ensure asset_tag is NULL if empty (to avoid Unique Constraint violations)
                if (!$assetTag) {
                    $asset->asset_tag = null;
                }
            }

            $asset->save();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Sync completed successfully!');
    }

    /**
     * Helper to compile hardware specifications into a JSON-ready array.
     * * @param GlpiComputer $comp
     * @return array
     */
    private function resolveHardwareSpecs($comp): array
    {
        // 1. RAM: Sum the size of all memory sticks
        $ramTotal = $comp->memoryItems->sum('size');
        // Fallback to main table if items are empty
        $ramStr = ($ramTotal > 0) ? $ramTotal . ' MB' : ($comp->memory ?? 0) . ' MB';

        // 2. CPU: Get the designation from the first processor item
        $cpuStr = 'Unknown Processor';
        if ($firstCpu = $comp->cpuItems->first()) {
            $cpuStr = $firstCpu->designation ?? 'Unknown CPU';
            if ($firstCpu->frequency > 0) {
                $cpuStr .= ' @ ' . $firstCpu->frequency . ' Mhz';
            }
        }

        // 3. Storage: Create a comma-separated list of drives and capacities
        $diskStr = 'No Storage';
        if ($comp->diskItems->isNotEmpty()) {
            $disks = $comp->diskItems->map(function ($disk) {
                $cap = $disk->capacity ?? 0;
                $capStr = ($cap > 1000) ? round($cap / 1024) . ' GB' : $cap . ' MB';
                return ($disk->designation ?? 'Disk') . " ($capStr)";
            })->toArray();
            $diskStr = implode(', ', $disks);
        }

        // 4. OS: Get detailed OS name from the relation
        $osStr = 'Unknown OS';
        if ($osItem = $comp->osItems->first()) {
            // Try getting name from Master table, fallback to pivot name
            $osStr = $osItem->operatingSystem->name ?? $osItem->name ?? 'Unknown OS';
        }

        return [
            'cpu' => $cpuStr,
            'ram' => $ramStr,
            'storage' => $diskStr,
            'os' => $osStr,
            'uuid' => $comp->uuid,
        ];
    }

    /**
     * Helper to sanitize Asset Tag.
     * Returns NULL if the tag is empty string to prevent Unique Constraint errors in DB.
     * * @param string|null $tag
     * @return string|null
     */
    private function resolveAssetTag($tag): ?string
    {
        if (empty($tag) || trim($tag) === '') {
            return null;
        }
        return trim($tag);
    }
}
