<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Models\Asset;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LoanRequestController extends Controller
{
    public function index()
    {
        // Admin melihat semua request, diurutkan yang Pending paling atas
        $requests = LoanRequest::with(['employee', 'asset'])
            ->orderByRaw("FIELD(status, 'PENDING', 'APPROVED', 'REJECTED')")
            ->latest()
            ->paginate(10);

        return Inertia::render('LoanRequests/Index', [
            'requests' => $requests
        ]);
    }

    public function approve(LoanRequest $loanRequest)
    {
        // Cek apakah aset masih available (takutnya sudah di-assign manual ke orang lain)
        if ($loanRequest->asset->status !== 'AVAILABLE') {
            return back()->withErrors(['asset' => 'Aset ini sudah tidak tersedia (sedang dipinjam/rusak).']);
        }

        DB::transaction(function () use ($loanRequest) {
            // 1. Update Status Request
            $loanRequest->update([
                'status' => 'APPROVED',
                'admin_id' => auth()->id(), // Siapa admin yang approve
                'reviewed_at' => now(),
            ]);

            // 2. Update Aset (Otomatis menjadi BORROWED)
            $asset = $loanRequest->asset;
            $oldStatus = $asset->status;
            
            $asset->update([
                'status' => 'BORROWED',
                // Kita asumsikan user yang request terhubung ke data employee
                // Jika user->employee_id ada, pakai itu. Jika tidak, null/error.
                'employee_id' => $loanRequest->employee_id, 
                'loan_type' => 'SHORT_TERM', // Request via web biasanya sementara
                'due_date' => $loanRequest->return_date,
            ]);

            // 3. Catat History
            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(), // Admin yang menyetujui
                'employee_id' => $loanRequest->employee_id,
                'action' => 'assign', // Actionnya assign karena barang keluar
                'status_before' => $oldStatus,
                'status_after' => 'BORROWED',
                'condition' => $asset->condition, // Kondisi saat ini
                'notes' => 'Self-Request Approved. Alasan: ' . $loanRequest->reason,
            ]);
        });

        return back()->with('success', 'Permintaan peminjaman disetujui. Aset kini berstatus BORROWED.');
    }

    public function reject(Request $request, LoanRequest $loanRequest)
    {
        $request->validate(['rejection_reason' => 'required|string']);

        $loanRequest->update([
            'status' => 'REJECTED',
            'admin_id' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Permintaan ditolak.');
    }

    public function create()
    {
        // Ambil hanya aset yang sedang AVAILABLE
        // Kita select field secukupnya agar ringan
        $assets = Asset::where('status', 'AVAILABLE')
            ->select('id', 'name', 'asset_tag', 'model')
            ->orderBy('name')
            ->get();

        return Inertia::render('LoanRequests/Create', [
            'assets' => $assets
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'due_date' => 'required|date|after:today', // Minimal besok
            'reason' => 'required|string|max:255',
        ]);

        // Cek Double Booking (Validasi Race Condition)
        // Pastikan aset masih AVAILABLE saat tombol diklik
        $asset = Asset::find($validated['asset_id']);
        if ($asset->status !== 'AVAILABLE') {
            return back()->withErrors(['asset_id' => 'Maaf, aset ini baru saja dipinjam orang lain.']);
        }

        // Simpan Request (Status: PENDING)
        LoanRequest::create([
            'user_id' => auth()->id(),
            'asset_id' => $validated['asset_id'],
            'due_date' => $validated['return_date'],
            'reason' => $validated['reason'],
            'status' => 'PENDING',
        ]);

        // Redirect ke dashboard atau halaman list request user
        return redirect()->route('dashboard')->with('success', 'Permintaan berhasil dikirim. Menunggu persetujuan Admin.');
    }
}