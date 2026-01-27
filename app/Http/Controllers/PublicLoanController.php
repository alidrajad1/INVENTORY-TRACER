<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicLoanController extends Controller
{
    public function create()
    {
        $assets = Asset::where('status', 'AVAILABLE')
            ->select('id', 'name', 'asset_tag', 'brand', 'model')
            ->orderBy('name')
            ->get();

        return Inertia::render('Publics/LoanRequest', [
            'assets' => $assets
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email'       => 'required|email|exists:employees,email',
            'asset_id'    => 'required|exists:assets,id',
            // 1. start_date harus hari ini atau setelahnya
            'start_date'  => 'required|date|after_or_equal:today', 
            // 2. return_date harus setelah atau sama dengan start_date (bukan cuma today)
            'due_date' => 'required|date|after_or_equal:start_date', 
            'reason'      => 'required|string|max:255',
        ], [
            'email.exists'                 => 'Email ini belum terdaftar di sistem.',
            'start_date.after_or_equal'    => 'Tanggal mulai tidak boleh tanggal lampau.',
            'due_date.after_or_equal'   => 'Tanggal kembali harus setelah atau sama dengan tanggal mulai.',
        ]);

        $employee = Employee::where('email', $validated['email'])->first();

        // Cek Ketersediaan Aset
        $asset = Asset::find($validated['asset_id']);
        if ($asset->status !== 'AVAILABLE') {
            return back()->withErrors(['asset_id' => 'Yah, aset ini baru saja dipinjam orang lain.']);
        }

        LoanRequest::create([
            'employee_id' => $employee->id,
            'asset_id'    => $validated['asset_id'],
            
            // PERBAIKAN BUG DISINI:
            // Sebelumnya Anda menulis: 'start_date' => $validated['return_date']
            'start_date'  => $validated['start_date'], // Gunakan input start_date yang benar
            
            'due_date' => $validated['due_date'],
            'reason'      => $validated['reason'],
            'status'      => 'PENDING',
        ]);

        return redirect()->back()->with('success', 'Permintaan terkirim! Silakan tunggu persetujuan Admin.');
    }
}