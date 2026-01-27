<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Location;
use App\Models\AssetHistory;
use App\Services\GlpiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AssetsExport;
use Maatwebsite\Excel\Facades\Excel;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::query()
            ->with([
                'category',
                'location',
                'employee',
                'histories' => function ($query) {
                    $query->latest()->limit(5);
                },
                'histories.user',
                'histories.employee'
            ])
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

        $stats = [
            'total' => Asset::count(),
            'available' => Asset::where('status', 'AVAILABLE')->count(),
            'maintenance' => Asset::where('status', 'MAINTENANCE')->count(),
        ];

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
            'employees' => Employee::where('is_active', true)->orderBy('name')->get(),
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
        // 1. Update Validasi: Tambahkan Vendor, Period (Garansi), & Purchase Year
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'required|exists:locations,id',
            'serial_number' => 'nullable|string|unique:assets,serial_number',
            'status' => 'nullable|in:AVAILABLE,BORROWED,MAINTENANCE,LOST,DISPOSED',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'hardware_specs' => 'nullable|array',

            // --- FIELD BARU (Aging & Vendor) ---
            'purchase_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'period' => 'nullable|integer|min:0|max:50', // Periode Garansi
            'vendor' => 'nullable|string|max:255',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = 'AVAILABLE';
        }

        Asset::create($validated);

        return redirect()->route('assets.index')->with('success', 'Aset berhasil didaftarkan.');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'category',
            'location',
            'employee',
            'histories.user',
            'histories.employee',
            'histories.location',
            'maintenance', // Pastikan relasi di model namanya 'maintenance' (hasMany)
            'loanRequest'  // Load request jika perlu
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
        // 2. Update Validasi Edit
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'location_id' => 'required|exists:locations,id',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'status' => 'nullable|in:AVAILABLE,BORROWED,MAINTENANCE,LOST,DISPOSED',
            'hardware_specs' => 'nullable|array',

            // --- FIELD BARU ---
            'purchase_year' => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'period' => 'nullable|integer|min:0|max:50',
            'vendor' => 'nullable|string|max:255',
        ]);

        $asset->update($validated);

        return redirect()->route('assets.index')->with('success', 'Data aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
    }

    // --- FITUR SMART LOAN (PEMINJAMAN) ---
    public function assign(Request $request, Asset $asset)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'location_id' => 'nullable|exists:locations,id',
            'notes' => 'nullable|string',

            // Validasi Tipe Peminjaman
            'loan_type' => 'required|in:LONG_TERM,SHORT_TERM',
            'due_date' => 'required_if:loan_type,SHORT_TERM|nullable|date|after:today',
        ]);

        $oldStatus = $asset->status;

        // Update Aset dengan Data Peminjaman
        $asset->update([
            'status' => 'BORROWED',
            'employee_id' => $request->employee_id,
            'loan_type' => $request->loan_type,
            // Jika SHORT_TERM, simpan tanggal. Jika LONG_TERM, null-kan.
            'due_date' => $request->loan_type === 'SHORT_TERM' ? $request->due_date : null,
        ]);

        // Catat History
        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => auth()->id(),
            'employee_id' => $request->employee_id,
            'action' => 'assign',
            'status_before' => $oldStatus,
            'status_after' => 'BORROWED',
            'condition' => 'GOOD',
            'location_id' => $request->location_id ?? $asset->location_id,
            // Tambahkan info tipe pinjam ke notes history
            'notes' => $request->notes . " [Tipe: " . $request->loan_type .
                ($request->loan_type === 'SHORT_TERM' ? ", Due: " . $request->due_date : "") . "]",
        ]);

        return back()->with('success', 'Aset berhasil dipinjamkan (' . $request->loan_type . ').');
    }

    public function returnAsset(Request $request, Asset $asset)
    {
        $request->validate([
            'condition' => 'required|in:GOOD,BAD,BROKEN', // Validasi input
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $asset->status;
        $lastEmployeeId = $asset->employee_id;

        // 1. Update Aset Utama (Status & Kondisi Terkini)
        $asset->update([
            'status' => 'AVAILABLE',
            'condition' => $request->condition, // <--- Simpan kondisi terkini
            'employee_id' => null,
            'loan_type' => null,
            'due_date' => null,
        ]);

        // 2. Simpan ke History (Rekam jejak kondisi saat dikembalikan)
        AssetHistory::create([
            'asset_id' => $asset->id,
            'user_id' => auth()->id(),
            'employee_id' => $lastEmployeeId,
            'action' => 'return',
            'status_before' => $oldStatus,
            'status_after' => 'AVAILABLE',
            'condition' => $request->condition, // <--- Masuk ke kolom yang Anda buat
            'notes' => $request->notes,
            'location_id' => $asset->location_id,
        ]);

        return back()->with('success', 'Aset telah dikembalikan. Kondisi: ' . $request->condition);
    }

    // --- GLPI & Printing Methods ---

    public function syncGlpi(Request $request, GlpiService $glpiService)
    {
        $request->validate(['serial_number' => 'required|string']);

        try {
            $data = $glpiService->searchBySerial($request->serial_number);

            if (!$data) {
                return back()->withErrors(['serial_number' => 'Serial Number tidak ditemukan di GLPI']);
            }
            return response()->json([
                'specs' => [
                    'brand' => $data['manufacturer'] ?? null,
                    'model' => $data['model'] ?? null,
                    'hardware_specs' => [
                        'cpu' => $data['cpu'] ?? null,
                        'ram' => $data['ram'] ?? null,
                        'storage' => $data['storage'] ?? $data['disk'] ?? null,
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
        $pdf = Pdf::loadView('pdf.sticker', ['asset' => $asset]);
        $pdf->setPaper([0, 0, 141.7, 85.0], 'portrait');
        return $pdf->stream('sticker-' . $asset->asset_tag . '.pdf');
    }

    public function printBatchLabel(Request $request)
    {
        $ids = $request->query('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada aset yang dipilih.');
        }

        $assets = Asset::whereIn('id', $ids)->get();
        $pdf = Pdf::loadView('pdf.sticker-batch', ['assets' => $assets]);
        $customPaper = [0, 0, 141.73, 85.03];
        $pdf->setPaper($customPaper, 'portrait');

        return $pdf->stream('batch-stickers.pdf');
    }

    public function export(Request $request)
    {
        // Nama file: assets-TANGGAL-JAM.xlsx
        $fileName = 'assets-' . date('Y-m-d-His') . '.xlsx';

        return Excel::download(new AssetsExport($request), $fileName);
    }
}