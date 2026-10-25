<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\ProductController;
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
        Route::view('/', 'admin.coming-soon', ['title' => 'Dashboard'])->name('dashboard');

        // Catálogo: solo admin
        Route::middleware('role:admin')->group(function () {
            Route::resource('categories', CategoryController::class)->only('index');
            Route::resource('brands', BrandController::class)->only('index');
            Route::resource('products', ProductController::class)->only('index');
        });
    });
});
