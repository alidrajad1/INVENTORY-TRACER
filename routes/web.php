<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Controllers
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoanRequestController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\PublicLoanController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Public Routes (Tanpa Login)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// --- Public Asset Request (Kiosk/QR) ---
Route::get('/request-asset', [PublicLoanController::class, 'create'])->name('public.loan.create');
Route::post('/request-asset', [PublicLoanController::class, 'store'])->name('public.loan.store');

// --- Mobile / Public Scanner ---
Route::resource('scan', MobileController::class)->only(['index', 'store']);
Route::prefix('scan')->group(function () {
    Route::get('/{tag}', [MobileController::class, 'show'])->name('public.scan');
    Route::post('/{tag}/checkin', [MobileController::class, 'checkin'])->name('public.checkin');
    Route::post('/{tag}/report', [MobileController::class, 'report'])->name('public.report');
});
// Resource scan opsional (jika butuh index/create scan), taruh setelah custom routes
Route::resource('scan', MobileController::class)->only(['index', 'store']); 


/*
|--------------------------------------------------------------------------
| Authenticated Routes (Wajib Login & Verified)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Asset Management ---
    // Note: Route statis (export, sync, batch) WAJIB ditaruh sebelum route resource/{id}
    Route::get('/assets/export', [AssetController::class, 'export'])->name('assets.export');
    Route::get('/assets/print-batch', [AssetController::class, 'printBatchLabel'])->name('assets.print-batch');
    Route::post('/assets/glpi-sync', [AssetController::class, 'syncGlpi'])->name('assets.sync');
    
    // Asset Actions
    Route::get('/assets/{asset}/print-label', [AssetController::class, 'printLabel'])->name('assets.print-label');
    Route::post('/assets/{asset}/return', [AssetController::class, 'returnAsset'])->name('assets.return');
    Route::put('/assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign');
    
    Route::resource('assets', AssetController::class);

    // --- Loan Requests (Approval) ---
    Route::post('/loan-requests/{loanRequest}/approve', [LoanRequestController::class, 'approve'])->name('loan-requests.approve');
    Route::post('/loan-requests/{loanRequest}/reject', [LoanRequestController::class, 'reject'])->name('loan-requests.reject');
    Route::resource('loan-requests', LoanRequestController::class);

    // --- Maintenance ---
    Route::get('/maintenances/export', [MaintenanceController::class, 'export'])->name('maintenances.export');
    Route::resource('maintenance-logs', MaintenanceController::class)->names('maintenances');

    // --- Audit ---
    // Resource sudah mencakup index dan store, route manual dihapus agar tidak duplikat
    Route::resource('audits', AuditController::class);

    // --- Master Data (Admin) ---
    Route::resource('users', UserController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('categories', CategoryController::class);

});

require __DIR__ . '/settings.php';