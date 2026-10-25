<?php

use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProfileController;
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
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');

    // Registro e inicio de sesión: límite de intentos por minuto
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:api-register')->name('register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:api-login')->name('login');

    // Cualquier usuario activo con token
    Route::middleware(['auth:sanctum', 'role:admin,comercial,cliente'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [ProfileController::class, 'show'])->name('me.show');
    });

    // Cliente
    Route::middleware(['auth:sanctum', 'role:cliente'])->group(function () {
        Route::put('me', [ProfileController::class, 'update'])->name('me.update');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{code}', [OrderController::class, 'show'])->name('orders.show');
        // Cancelar es un cambio de estado, no un borrado
        Route::patch('orders/{code}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    });

    // Administrador
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::apiResource('products', AdminProductController::class);
    });
});
