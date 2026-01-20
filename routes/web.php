<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ScanController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::post('/assets/{asset}/return', [AssetController::class, 'returnAsset'])->name('assets.return');
    Route::post('/assets/{asset}/assign', [AssetController::class, 'assign'])->name('assets.assign');
    Route::post('/assets/glpi-sync', [AssetController::class, 'syncGlpi'])->name('assets.sync');
    Route::get('/assets/{asset}/print-label', [AssetController::class, 'printLabel'])->name('assets.print-label');
    Route::get('/assets/print-batch', [AssetController::class, 'printBatchLabel'])->name('assets.print-batch');
    Route::resource('assets', AssetController::class);

    Route::resource('users', UserController::class);

    Route::resource('employees', EmployeeController::class);

    Route::resource('locations', LocationController::class);

    Route::resource('categories', CategoryController::class);

    Route::resource('maintenance-logs', MaintenanceController::class)
        ->names('maintenances');

    Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    Route::post('audits', [AuditController::class, 'store'])->name('audits.store');
    Route::resource('audits', AuditController::class);


    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::get('/scan/{asset_tag}', [ScanController::class, 'show'])->name('scan.result');
    Route::post('/scan/transaction', [ScanController::class, 'store'])->name('scan.transaction');

});

require __DIR__ . '/settings.php';
