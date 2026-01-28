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
        if ($loanRequest->asset->status !== 'AVAILABLE') {
            return back()->withErrors(['asset' => 'Aset ini sudah tidak tersedia (sedang dipinjam/rusak).']);
        }

        DB::transaction(function () use ($loanRequest) {
            $loanRequest->update([
                'status' => 'APPROVED',
                'admin_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            $asset = $loanRequest->asset;
            $oldStatus = $asset->status;
            
            $asset->update([
                'status' => 'BORROWED',
                'employee_id' => $loanRequest->employee_id, 
                'loan_type' => 'SHORT_TERM',
                'due_date' => $loanRequest->due_date,
            ]);

            AssetHistory::create([
                'asset_id' => $asset->id,
                'user_id' => auth()->id(),
                'employee_id' => $loanRequest->employee_id,
                'action' => 'assign', 
                'status_before' => $oldStatus,
                'status_after' => 'BORROWED',
                'condition' => $asset->condition,
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
            'due_date' => 'required|date|after:today',
            'reason' => 'required|string|max:255',
        ]);

        $asset = Asset::find($validated['asset_id']);
        if ($asset->status !== 'AVAILABLE') {
            return back()->withErrors(['asset_id' => 'Maaf, aset ini baru saja dipinjam orang lain.']);
        }

        LoanRequest::create([
            'user_id' => auth()->id(),
            'asset_id' => $validated['asset_id'],
            'due_date' => $validated['due_date'],
            'reason' => $validated['reason'],
            'status' => 'PENDING',
        ]);

        return redirect()->route('dashboard')->with('success', 'Permintaan berhasil dikirim. Menunggu persetujuan Admin.');
    }
}