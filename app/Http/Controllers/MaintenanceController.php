<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MaintenancesExport;

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
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'description' => 'required|string',
            'scheduled_at' => 'required|date',
            'status' => 'required|in:scheduled,in_progress,completed,canceled',
        ]);

        $data = $request;
        $data['user_id'] = auth()->id();
        Maintenance::create($request->all());

        return redirect()->back()->with('success', 'Maintenance scheduled successfully.');
    }

public function update(Request $request, $id)
{
    // dd($request->all());
    // Cari manual, agar pasti ketemu
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

    $maintenance->update($validated);

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