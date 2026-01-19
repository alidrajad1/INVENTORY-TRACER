<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('building', 'like', '%' . $request->search . '%');
            });
        }

        $locations = $query->withCount('assets') // Hitung jumlah aset di lokasi ini
            ->orderBy('name', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'building' => 'required|string|max:255',
        ]);

        Location::create([
            'name' => $request->name,
            'building' => $request->building,
        ]);

        return redirect()->back()->with('success', 'Location created successfully.');
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'building' => 'required|string|max:255',
        ]);

        $location->update([
            'name' => $request->name,
            'building' => $request->building,
        ]);

        return redirect()->back()->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        if ($location->assets()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete location because it has related assets.']);
        }

        $location->delete();

        return redirect()->back()->with('success', 'Location deleted successfully.');
    }
}