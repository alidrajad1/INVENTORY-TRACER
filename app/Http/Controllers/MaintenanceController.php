<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Maintenance;
use App\Models\AssetHistory; 
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaintenancesExport;
use Illuminate\Support\Facades\DB; 

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Maintenance::query()->with('asset');

        if ($request->search) {
            $query->where('description', 'like', '%' . $request->search . '%')
                ->orWhereHas('asset', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('asset_tag', 'like', '%' . $request->search . '%');
                });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $maintenances = $query->orderBy('scheduled_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $assets = Asset::select('id', 'name', 'asset_tag')->orderBy('name')->get();

        return Inertia::render('Maintenances/Index', [
            'maintenances' => $maintenances,
            'assets' => $assets,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'description' => 'required|string',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:scheduled,in_progress,completed,canceled',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $data = $validated;
            $data['user_id'] = auth()->id();
            Maintenance::create($data);

            $asset = Asset::findOrFail($validated['asset_id']);
            $oldStatus = $asset->status;

            if ($oldStatus !== 'MAINTENANCE') {
                $asset->update([
                    'status' => 'MAINTENANCE',
                    'employee_id' => null, 
                    'loan_type' => null,
                    'due_date' => null,
                ]);

                AssetHistory::create([
                    'asset_id' => $asset->id,
                    'user_id' => auth()->id(),
                    'action' => 'send_repair',
                    'status_before' => $oldStatus,
                    'status_after' => 'MAINTENANCE',
                    'condition' => $asset->condition,
                    'location_id' => $asset->location_id,
                    'notes' => 'Maintenance Scheduled: ' . $request->description,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Maintenance scheduled & Asset set to MAINTENANCE.');
    }

    public function update(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);

        if ($request->status === 'completed' && empty($request->completed_at)) {
            $request->merge(['completed_at' => now()]);
        }
        if ($request->status !== 'completed') {
            $request->merge(['completed_at' => null]);
        }

        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'description' => 'required|string',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:scheduled,in_progress,completed,canceled',
            'completed_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($maintenance, $validated, $request) {

            $maintenance->update($validated);

            if (in_array($request->status, ['completed', 'canceled'])) {
                $asset = Asset::findOrFail($maintenance->asset_id);

                if ($asset->status === 'MAINTENANCE') {
                    $asset->update(['status' => 'AVAILABLE']);

                    AssetHistory::create([
                        'asset_id' => $asset->id,
                        'user_id' => auth()->id(),
                        'action' => 'maintenance_finished',
                        'status_before' => 'MAINTENANCE',
                        'status_after' => 'AVAILABLE',
                        'condition' => 'GOOD', 
                        'location_id' => $asset->location_id,
                        'notes' => 'Maintenance status: ' . $request->status,
                    ]);
                }
            }
        });

        return redirect()->back()->with('success', 'Maintenance updated successfully.');
    }

    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();
        return redirect()->back()->with('success', 'Maintenance record deleted.');
    }

    public function export(Request $request)
    {
        $fileName = 'maintenance-report-' . date('Y-m-d') . '.xlsx';
        return Excel::download(new MaintenancesExport($request), $fileName);
    }
}