<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nid', 'like', '%' . $request->search . '%') // Cari via NIK/NID
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // withCount('assets') untuk melihat aset yang sedang dipinjam
        $employees = $query->withCount('assets')
            ->with([
                'histories.assets' => function ($q) {
                    $q->select('id', 'name', 'asset_tag');
                }
            ])
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nid' => 'required|string|max:20|unique:employees,nid',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        Employee::create($validated);

        return redirect()->back()->with('success', 'Employee created successfully.');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'nid' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('employees')->ignore($employee->id)],
            'position' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        $employee->update($validated);

        return redirect()->back()->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        // Cek apakah employee masih memegang aset
        if ($employee->assets()->where('status', 'BORROWED')->exists()) {
            return back()->withErrors(['error' => 'Cannot delete employee. They still have assigned assets.']);
        }

        $employee->delete();

        return redirect()->back()->with('success', 'Employee deleted successfully.');
    }

    public function show(Employee $employee)
    {
        // Load history pegawai lengkap dengan detail asetnya
        $employee->load([
            'histories.asset',
            'histories.user'
        ]);

        return Inertia::render('Employees/Show', [
            'employee' => $employee,
            'asset_history' => $employee->histories
        ]);
    }
}