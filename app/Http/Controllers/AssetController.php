<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Services\GlpiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Data Aset (Search & Filter)
        $assets = Asset::query()
            ->with(['category', 'location', 'employee'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_tag', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // 2. Hitung Statistik
        $stats = [
            'total' => Asset::count(),
            'available' => Asset::where('status', 'AVAILABLE')->count(),
            'maintenance' => Asset::where('status', 'MAINTENANCE')->count(),
        ];

        // 3. Render ke Vue
        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
            'categories' => Category::select('id', 'name')->orderBy('name')->get(),
            'locations' => Location::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Assets/Create', [
            'categories' => Category::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'required|exists:locations,id',
            'serial_number' => 'nullable|string|unique:assets,serial_number',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'hardware_specs' => 'nullable|array', // JSON dari GLPI
        ]);

        // Status default
        $validated['status'] = 'AVAILABLE';

        // Asset tag akan digenerate otomatis oleh Model (Boot method)
        Asset::create($validated);

        return redirect()->route('assets.index')->with('success', 'Aset berhasil didaftarkan.');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'category',
            'location',
            'histories.user', 
            'histories.employee',
            'histories.location',
            'maintenances'
            ]);

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
            'histories' => $asset->histories
        ]);
    }

    public function edit(Asset $asset)
    {
        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'categories' => Category::orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'required|exists:locations,id',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            // Tambahkan ini agar saat Edit -> Sync GLPI, data spek baru bisa tersimpan
            'hardware_specs' => 'nullable|array',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
    }

    // --- GLPI SYNC FUNCTION (Updated) ---
    public function syncGlpi(Request $request, GlpiService $glpiService)
    {
        $request->validate(['serial_number' => 'required|string']);

        try {
            // Pastikan GlpiService->searchBySerial() mengembalikan array lengkap termasuk 'os'
            $data = $glpiService->searchBySerial($request->serial_number);

            if (!$data) {
                return back()->withErrors(['serial_number' => 'Serial Number tidak ditemukan di GLPI']);
            }

            // Return JSON ke Vue untuk autofill form
            return response()->json([
                'specs' => [
                    'brand' => $data['manufacturer'] ?? null,
                    'model' => $data['model'] ?? null,
                    'hardware_specs' => [
                        'cpu' => $data['cpu'] ?? null,
                        'ram' => $data['ram'] ?? null,
                        // Sesuaikan key dengan output Command ('storage' bukan 'disk')
                        'storage' => $data['storage'] ?? $data['disk'] ?? null,
                        // Tambahkan OS di sini
                        'os' => $data['os'] ?? null,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['api' => 'Gagal terhubung ke GLPI: ' . $e->getMessage()]);
        }
    }
    public function printLabel(Asset $asset)
    {
        // Load view khusus untuk label
        $pdf = Pdf::loadView('pdf.sticker', ['asset' => $asset]);

        // Set ukuran kertas custom (contoh: 5cm x 3cm)
        // Format: [0, 0, width_in_points, height_in_points]
        // 1 cm = 28.35 poin
        // Jadi 5cm x 3cm = [0, 0, 141.7, 85.0]
        $pdf->setPaper([0, 0, 141.7, 85.0], 'portrait');

        // Render dan stream (buka di browser)
        return $pdf->stream('sticker-' . $asset->asset_tag . '.pdf');
    }

    public function printBatchLabel(Request $request)
    {
        // 1. Ambil ID dari query string ?ids[]=1&ids[]=2
        $ids = $request->query('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada aset yang dipilih.');
        }

        // 2. Ambil data aset berdasarkan ID
        $assets = Asset::whereIn('id', $ids)->get();

        // 3. Load view khusus bulk
        $pdf = Pdf::loadView('pdf.sticker-batch', ['assets' => $assets]);

        // 4. Set ukuran kertas (sama seperti single label: 50mm x 30mm)
        $customPaper = [0, 0, 141.73, 85.03];
        $pdf->setPaper($customPaper, 'portrait');

        return $pdf->stream('batch-stickers.pdf');
    }

    public function assign(Request $request, Asset $asset)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'notes' => 'nullable|string',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        // 1. Simpan Status Lama
        $oldStatus = $asset->status;

        // 2. Update Aset Utama
        $asset->update([
            'status' => 'BORROWED',
            // Jika di tabel asset ada kolom employee_id (current holder), update juga:
            // 'employee_id' => $request->employee_id 
        ]);

        // 3. Catat di History
        \App\Models\AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => auth()->id(), // ID Admin yang sedang login
            'employee_id' => $request->employee_id,
            'action' => 'assign',
            'status_before' => $oldStatus,
            'status_after' => 'BORROWED',
            'condition' => 'GOOD', // Asumsi barang keluar kondisi bagus
            'location_id' => $request->location_id,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Aset berhasil dipinjamkan');
    }
    public function returnAsset(Request $request, Asset $asset)
    {
        $request->validate([
            'condition' => 'required|in:GOOD,BAD,BROKEN', // Cek kondisi saat kembali
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $asset->status;

        // 1. Update Aset Utama
        $asset->update([
            'status' => 'AVAILABLE',
            // Kosongkan pemegang saat ini
            // 'employee_id' => null 
        ]);

        // 2. Catat History Pengembalian
        \App\Models\AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => auth()->id(),
            // Ambil employee terakhir dari history assign terakhir, atau dari request jika diinput manual
            // Disini kita asumsikan null karena aset sudah kembali ke gudang, 
            // ATAU kita isi ID pegawai yang mengembalikan untuk record.
            'employee_id' => $request->employee_id,
            'action' => 'return',
            'status_before' => $oldStatus,
            'status_after' => 'AVAILABLE',
            'condition' => $request->condition,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Aset telah dikembalikan');
    }
}