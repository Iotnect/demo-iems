<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\MonitoringController;
use App\Http\Middleware\EnsureDemoAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');

Route::middleware(EnsureDemoAuthenticated::class)->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', [MonitoringController::class, 'index'])->name('iems.overview');
    Route::post('/administration/settings', [MonitoringController::class, 'settings'])->name('iems.settings');
    Route::post('/alerts/{anomaly}/review', [MonitoringController::class, 'review'])->name('iems.review');
    Route::get('/reports/print', [MonitoringController::class, 'report'])->name('iems.report');
    Route::get('/observations/export', [MonitoringController::class, 'export'])->name('iems.export');
    Route::get('/{screen}', [MonitoringController::class, 'index'])
        ->whereIn('screen', ['overview', 'areas-of-interest', 'monitoring', 'historical-baseline', 'alerts', 'reports', 'poc-evaluation', 'administration'])
        ->name('iems.screen');
});
