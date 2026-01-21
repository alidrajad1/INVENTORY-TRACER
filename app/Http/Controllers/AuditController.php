<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{

    public function index(Request $request)
    {
        $query = Asset::query()->with(['category', 'location']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('asset_tag', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        $query->addSelect([
            'last_audit_condition' => AssetHistory::select('condition')
                ->whereColumn('asset_id', 'assets.id')
                ->where('action', 'audit') 
                ->latest()
                ->limit(1)
        ])->addSelect('assets.*'); 

        $assets = $query->orderByRaw('last_audit_date IS NULL DESC')
            ->orderBy('last_audit_date', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Audits/Index', [
            'assets' => $assets,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'condition' => 'required|in:GOOD,BAD,BROKEN',
            'notes' => 'nullable|string',
            'location_id' => 'nullable|exists:locations,id', 
        ]);

        DB::transaction(function () use ($request) {
            $asset = Asset::findOrFail($request->asset_id);
            
            $statusBefore = $asset->status;
            $oldLocation = $asset->location_id;

            $updateData = ['last_audit_date' => now()];
            
            if ($request->location_id && $request->location_id != $oldLocation) {
                $updateData['location_id'] = $request->location_id;
            }
            
            $asset->update($updateData);

            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'employee_id' => $asset->employee_id, 
                'action' => 'audit',
                'status_before' => $statusBefore,
                'status_after' => $statusBefore, 
                'condition' => $request->condition,
                'location_id' => $request->location_id ?? $oldLocation,
                'notes' => $request->notes,
            ]);
        });

        return redirect()->back()->with('success', 'Aset berhasil diverifikasi.');
    }
}