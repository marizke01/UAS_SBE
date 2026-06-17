<?php

use App\Http\Controllers\Presentation\DemoController;
use App\Http\Controllers\Presentation\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DemoController::class, 'home'])->name('home');
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DemoController::class, 'dashboard'])->name('dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/inventory', [DemoController::class, 'inventory'])->name('inventory');
    Route::get('/invoices', [DemoController::class, 'invoices'])->name('invoices');
    Route::get('/reports', [DemoController::class, 'reports'])->name('reports');
    Route::get('/ai-analytics', [DemoController::class, 'ai'])->name('ai');
});
