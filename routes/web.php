<?php

use App\Http\Controllers\Presentation\AdminAuthController;
use App\Http\Controllers\Presentation\DemoController;
use App\Http\Controllers\Presentation\InvoiceController;
use App\Http\Controllers\Presentation\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DemoController::class, 'home'])->name('home');
Route::get('/checkout', [DemoController::class, 'checkout'])->name('public.checkout.page');
Route::post('/checkout', [PosController::class, 'publicCheckout'])->name('public.checkout');
Route::get('/invoice/{invoice}/download', [InvoiceController::class, 'buyer'])->name('public.invoice.download');

Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::get('/', [DemoController::class, 'dashboard'])->name('dashboard');
    Route::get('/stocks/modal-data', [DemoController::class, 'stockModalData'])->name('stocks.modal-data');
    Route::post('/stocks/update', [DemoController::class, 'updateStock'])->name('stocks.update');
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/inventory', [DemoController::class, 'inventory'])->name('inventory');
    Route::get('/website-orders', [DemoController::class, 'websiteOrders'])->name('website-orders');
    Route::post('/website-orders/{sale}/status', [DemoController::class, 'updateWebsiteOrderStatus'])->name('website-orders.status');
    Route::get('/invoices', [DemoController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'buyer'])->name('invoices.buyer.download');
    Route::get('/seller-invoices/transaction/{sale}', [InvoiceController::class, 'sellerTransaction'])->name('seller-invoices.transaction');
    Route::get('/seller-invoices/daily', [InvoiceController::class, 'sellerDaily'])->name('seller-invoices.daily');
    Route::get('/seller-invoices/full', [InvoiceController::class, 'sellerFull'])->name('seller-invoices.full');
    Route::get('/reports', [DemoController::class, 'reports'])->name('reports');
    Route::get('/exports/sales', [DemoController::class, 'exportSalesCsv'])->name('exports.sales');
    Route::get('/exports/stock', [DemoController::class, 'exportStockCsv'])->name('exports.stock');
    Route::get('/exports/best-products', [DemoController::class, 'exportBestProductsCsv'])->name('exports.best-products');
    Route::get('/ai-analytics', [DemoController::class, 'ai'])->name('ai');
});
