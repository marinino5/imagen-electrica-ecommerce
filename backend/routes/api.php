<?php

use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DatasheetController;
use App\Http\Controllers\Api\V1\LineController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ShippingController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\GuestOrderController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\QuoteRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API REST v1 (api.v1.*) — se publica bajo /api/v1
|--------------------------------------------------------------------------
| Autenticación con token de Sanctum (Authorization: Bearer ...).
*/
Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {
    // Catálogo público: sin token
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('families', [LineController::class, 'families'])->name('families.index');
    Route::get('promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('lines', [LineController::class, 'index'])->name('lines.index');
    Route::get('lines/{slug}', [LineController::class, 'show'])->name('lines.show');
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');

    // Departamentos y municipios (DANE) para los formularios de facturación y entrega
    Route::get('locations/departments', [LocationController::class, 'departments'])->name('locations.departments');
    Route::get('locations/departments/{code}/municipalities', [LocationController::class, 'municipalities'])->name('locations.municipalities');

    Route::get('shipping/quote', [ShippingController::class, 'quote'])->name('shipping.quote');

    // Comprar sin cuenta (o con ella, si llega el token): datos de facturación electrónica y entrega. Pública, con límite por IP
    Route::post('checkout', [CheckoutController::class, 'store'])->middleware('throttle:api-checkout')->name('checkout.store');
    // Pedido de invitado: se abre con el enlace firmado del correo
    Route::get('guest/orders/{code}', [GuestOrderController::class, 'show'])->middleware('throttle:api-guest')->name('guest.orders.show');
    Route::post('guest/orders/{code}/account', [GuestOrderController::class, 'account'])->middleware('throttle:api-register')->name('guest.orders.account');

    // Cotización sin cuenta: pública, con límite por IP
    Route::post('quote-requests', [QuoteRequestController::class, 'store'])->middleware('throttle:api-quote')->name('quote-requests.store');
    Route::post('quote-requests/free', [QuoteRequestController::class, 'storeFree'])->middleware('throttle:api-quote')->name('quote-requests.store-free');

    // Ficha técnica con cotización en PDF: pública (el catálogo no pide token), con límite por IP
    Route::get('datasheet', [DatasheetController::class, 'show'])->middleware('throttle:api-datasheet')->name('datasheet.show');

    // Boletín desde el pie de la tienda: público, con límite por IP
    Route::post('newsletter', [NewsletterController::class, 'store'])->middleware('throttle:api-newsletter')->name('newsletter.store');

    // Registro e inicio de sesión: límite de intentos por minuto
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:api-register')->name('register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:api-login')->name('login');

    // Recuperar la contraseña: pública, con límite por IP
    Route::post('password/forgot', [AuthController::class, 'forgotPassword'])->middleware('throttle:api-password-forgot')->name('password.forgot');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->middleware('throttle:api-password-reset')->name('password.reset');

    // Cualquier usuario activo con token
    Route::middleware(['auth:sanctum', 'role:admin,comercial,cliente'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [ProfileController::class, 'show'])->name('me.show');
    });

    // Cliente
    Route::middleware(['auth:sanctum', 'role:cliente'])->group(function () {
        Route::put('me', [ProfileController::class, 'update'])->name('me.update');
        Route::put('me/billing', [ProfileController::class, 'updateBilling'])->name('me.billing');
        Route::put('me/addresses', [ProfileController::class, 'updateAddresses'])->name('me.addresses');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{code}', [OrderController::class, 'show'])->name('orders.show');
        // Cancelar es un cambio de estado, no un borrado
        Route::patch('orders/{code}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // Administrador
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('categories', AdminCategoryController::class);
    });
});
