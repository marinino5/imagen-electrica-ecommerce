<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DatasheetDownloadController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\OrderAssignmentController;
use App\Http\Controllers\Admin\QuoteInboxController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderPricingController;
use App\Http\Controllers\Admin\OrderStatusController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\QuoteRequestController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Middleware\RememberPanelPeriod;
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
        // Inicio y Análisis comparten el filtro de período de la barra superior (se recuerda en la sesión)
        Route::get('/', DashboardController::class)->middleware(RememberPanelPeriod::class)->name('dashboard');
        // Ruta alternativa: /admin/dashboard lleva al mismo dashboard
        Route::redirect('dashboard', '/admin');

        // Buscador global (productos): lo usan admin y comercial
        Route::get('search', SearchController::class)->name('search');

        // Pedidos y cotizaciones: admin y comercial
        Route::resource('orders', OrderController::class)->only(['index', 'show']);
        Route::patch('orders/{order}/status', OrderStatusController::class)->name('orders.status');
        Route::patch('orders/{order}/assignment', OrderAssignmentController::class)->name('orders.assignment');
        Route::put('orders/{order}/prices', OrderPricingController::class)->name('orders.prices');

        // Cotizaciones enviadas sin cuenta desde la tienda: admin y comercial
        Route::get('quote-requests', [QuoteRequestController::class, 'index'])->name('quote-requests.index');
        Route::get('quote-requests/{quoteRequest}', [QuoteRequestController::class, 'show'])->name('quote-requests.show');
        Route::get('quote-requests/{quoteRequest}/attachment', [QuoteRequestController::class, 'attachment'])->name('quote-requests.attachment');
        Route::patch('quote-requests/{quoteRequest}', [QuoteRequestController::class, 'update'])->name('quote-requests.update');

        // Suscriptores del boletín (pie de la tienda): admin y comercial
        Route::get('newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
        Route::get('newsletter/export', [NewsletterController::class, 'export'])->name('newsletter.export');
        Route::patch('newsletter/{subscription}/unsubscribe', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

        // Cotizaciones de las dos fuentes (con y sin cuenta) en una sola bandeja, y clientes con su historial
        Route::get('cotizaciones', QuoteInboxController::class.'@index')->name('quotes.index');
        Route::resource('customers', CustomerController::class)->only(['index', 'show']);
        Route::get('datasheets', [DatasheetDownloadController::class, 'index'])->name('datasheets.index');

        // Catálogo y análisis: solo admin
        Route::middleware('role:admin')->group(function () {
            Route::resource('categories', CategoryController::class)->except('show');
            Route::resource('brands', BrandController::class)->except('show');
            Route::resource('promotions', PromotionController::class)->except('show');
            Route::resource('products', ProductController::class);
            // Los productos eliminados (borrado lógico) no los resuelve la ruta sin withTrashed
            Route::patch('products/{product}/restore', [ProductController::class, 'restore'])
                ->withTrashed()->name('products.restore');

            // Análisis: reportes con costos y ganancia, solo admin
            Route::middleware(RememberPanelPeriod::class)->group(function () {
                Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
                Route::get('reports/comercial', [ReportController::class, 'commercial'])->name('reports.commercial');
                Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
                Route::get('reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
                Route::get('reports/comercial/export', [ReportController::class, 'commercialExport'])->name('reports.commercial.export');
                Route::get('reports/comercial/pdf', [ReportController::class, 'commercialPdf'])->name('reports.commercial.pdf');
            });
        });
    });
});
