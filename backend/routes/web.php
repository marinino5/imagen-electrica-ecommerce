<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\OrderAssignmentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderPricingController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchController;
use Illuminate\Support\Facades\Route;

// La tienda del cliente es la app React (/cliente); el backend solo sirve el panel.
Route::redirect('/', '/admin');

/*
|--------------------------------------------------------------------------
| Panel administrativo (admin.*)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        // Máximo 5 intentos por minuto para frenar ataques de fuerza bruta
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

    // Personal: admin y comercial
    Route::middleware(['auth', 'role:admin,comercial'])->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        // Ruta alternativa: /admin/dashboard lleva al mismo dashboard
        Route::redirect('dashboard', '/admin');

        // Buscador global (productos): lo usan admin y comercial
        Route::get('search', SearchController::class)->name('search');

        // Pedidos y cotizaciones: admin y comercial
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', OrderStatusController::class)->name('orders.status');
        Route::patch('orders/{order}/assignment', OrderAssignmentController::class)->name('orders.assignment');
        Route::put('orders/{order}/prices', OrderPricingController::class)->name('orders.prices');

        // Catálogo y análisis: solo admin
        Route::middleware('role:admin')->group(function () {
            Route::resource('categories', CategoryController::class)->except('show');
            Route::resource('brands', BrandController::class)->except('show');
            Route::resource('products', ProductController::class);
            // Los productos eliminados (borrado lógico) no los resuelve la ruta sin withTrashed
            Route::patch('products/{product}/restore', [ProductController::class, 'restore'])
                ->withTrashed()->name('products.restore');

            // Análisis: reportes con costos y ganancia, solo admin
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        });
    });
});
