<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    /**
     * Menampilkan daftar aset untuk diaudit.
     * Diurutkan berdasarkan yang paling lama tidak dicek.
     */
    public function index(Request $request)
    {
        $query = Asset::query()->with(['category', 'location']);

        // 1. Filter Pencarian
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('asset_tag', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Subquery: Ambil Kondisi Terakhir dari History Audit
        // Ini akan membuat kolom virtual 'last_audit_condition' di hasil query
        $query->addSelect([
            'last_audit_condition' => AssetHistory::select('condition')
                ->whereColumn('asset_id', 'assets.id')
                ->where('action', 'audit') // Hanya ambil history tipe audit
                ->latest()
                ->limit(1)
        ])->addSelect('assets.*'); // Penting: Tetap ambil semua kolom assets

        // 3. Sorting: Prioritaskan yang belum pernah diaudit (NULL), lalu berdasarkan tanggal terlama
        $assets = $query->orderByRaw('last_audit_date IS NULL DESC')
            ->orderBy('last_audit_date', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Audits/Index', [
            'assets' => $assets,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Menyimpan hasil Audit (Verifikasi)
     */
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

            // 1. Update Aset Utama
            // Update tanggal audit & lokasi (jika berubah)
            $updateData = ['last_audit_date' => now()];
            
            if ($request->location_id && $request->location_id != $oldLocation) {
                $updateData['location_id'] = $request->location_id;
            }
            
            $asset->update($updateData);

            // 2. Catat History (Audit Trail)
            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'employee_id' => $asset->employee_id, // Tetap gunakan pemegang saat ini
                'action' => 'audit',
                'status_before' => $statusBefore,
                'status_after' => $statusBefore, // Audit tidak mengubah status (AVAILABLE/BORROWED)
                'condition' => $request->condition, // Simpan kondisi fisik hasil cek
                'location_id' => $request->location_id ?? $oldLocation,
                'notes' => $request->notes,
            ]);
        });

        return redirect()->back()->with('success', 'Aset berhasil diverifikasi.');
    }
}