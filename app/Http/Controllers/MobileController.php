<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MobileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Mobile/Index');
    }

    /**
     * Menampilkan Halaman Publik Aset (Tanpa Login).
     * URL: /scan/{tag}
     */
    public function show($tag)
    {
        $asset = Asset::where('asset_tag', $tag)
            ->with(['location', 'employee'])
            ->firstOrFail();

        return Inertia::render('Mobile/AssetInfo', [
            'asset' => [
                'id' => $asset->id,
                'name' => $asset->name,            // Header: Nama Aset
                'asset_tag' => $asset->asset_tag,  // Header: Tag
                'status' => $asset->status,        // Header: Status
                'image' => $asset->image,
                'location' => $asset->location ? $asset->location->name : '-', // Tengah: Lokasi
                'user_name' => $asset->employee ? $asset->employee->name : 'Tidak Ada (Available)', // Tengah: Pemegang
                'specs' => $asset->hardware_specs, // Tengah: Spesifikasi (RAM/CPU)
                'manual_link' => $asset->manual_link, // Tombol: Manual
            ]
        ]);
    }

    /**
     * Aksi: "Saya Pegang Aset Ini" (Audit Mandiri)
     */
    public function checkin($tag)
    {
        $asset = Asset::where('asset_tag', $tag)->firstOrFail();

        DB::transaction(function () use ($asset) {
            // Update tanggal audit
            $asset->update(['last_audit_date' => now()]);

            // Catat history (User ID 1 = System/Admin default)
            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => 1,
                'employee_id' => $asset->employee_id,
                'action' => 'audit',
                'status_before' => $asset->status,
                'status_after' => $asset->status,
                'condition' => 'GOOD',
                'notes' => 'Self Check-in via Public Scan',
            ]);
        });

        return back()->with('success', 'Konfirmasi berhasil! Audit tercatat.');
    }

    /**
     * Aksi: "Lapor Isu" (Maintenance)
     */
    public function report(Request $request, $tag)
    {
        $request->validate([
            'reporter_name' => 'required|string|max:50',
            'issue' => 'required|string|max:500',
        ]);

        $asset = Asset::where('asset_tag', $tag)->firstOrFail();

        Maintenance::create([
            'asset_id' => $asset->id,
            'user_id' => 1,
            'vendor_name' => 'Reported by: ' . $request->reporter_name,
            'description' => $request->issue,
            'status' => 'scheduled',
            'start_date' => now(),
        ]);

        return back()->with('success', 'Laporan dikirim. Tim IT akan segera mengecek.');
    }
}
