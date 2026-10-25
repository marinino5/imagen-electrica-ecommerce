<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Los enlaces de paginación usan las clases de Bootstrap del panel
        Paginator::useBootstrapFive();

        $this->configureApiRateLimits();

        // Saludo, fecha y campana de la barra superior del panel
        View::composer('layouts.admin', AdminLayoutComposer::class);
    }

    /**
     * Límites de la API. Al superarlos responde 429 con el encabezado Retry-After.
     * Login y registro son más estrictos para frenar ataques de fuerza bruta.
     */
    private function configureApiRateLimits(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));

        // Por correo y dirección IP: adivinar la clave de una cuenta no se logra cambiando de correo ni de IP a la vez
        RateLimiter::for('api-login', fn (Request $request) => Limit::perMinute(5)
            ->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()));

        RateLimiter::for('api-register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
    }
}
