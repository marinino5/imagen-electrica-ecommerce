<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use Illuminate\Pagination\Paginator;
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

        // Saludo, fecha y campana de la barra superior del panel
        View::composer('layouts.admin', AdminLayoutComposer::class);
    }
}
