<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index()
{
    $logs = Activity::with('causer')
        ->latest()
        ->paginate(20);

    return Inertia::render('Logs/Index', ['logs' => $logs]);
}
}
