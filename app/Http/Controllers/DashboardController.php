<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\Maintenance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAssets = Asset::count();
        $borrowedAssets = Asset::where('status', 'BORROWED')->count();
        $maintenanceAssets = Asset::where('status', 'MAINTENANCE')->count();
        $availableAssets = Asset::where('status', 'AVAILABLE')->count();

        $auditOverdue = Asset::where(function ($q) {
            $q->whereNull('last_audit_date')
              ->orWhere('last_audit_date', '<', now()->subMonths(3));
        })->count();

        $pendingMaintenance = Maintenance::where('status', 'PENDING')->count();

        $recentActivities = AssetHistory::with(['asset', 'user', 'employee'])
            ->latest()
            ->limit(6)
            ->get();

        $categories = \App\Models\Category::withCount('assets')
            ->orderByDesc('assets_count')
            ->limit(5)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalAssets,
                'borrowed' => $borrowedAssets,
                'maintenance' => $maintenanceAssets,
                'available' => $availableAssets,
                'audit_overdue' => $auditOverdue,
                'maintenance_pending' => $pendingMaintenance,
            ],
            'activities' => $recentActivities,
            'top_categories' => $categories,
        ]);
    }
}