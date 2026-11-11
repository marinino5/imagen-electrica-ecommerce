<?php

namespace App\Providers;

use App\View\Composers\AdminLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\MigrationEnded;
use Illuminate\Database\Events\MigrationStarted;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
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
        $this->logMigrations();

        // Saludo, fecha y campana de la barra superior del panel
        View::composer('layouts.admin', AdminLayoutComposer::class);
    }

    /**
     * Una línea con hora al empezar y otra al terminar cada migración. Laravel imprime el nombre solo cuando la migración termina,
     * y el registro de Render muestra líneas completas: sin esto una migración lenta o colgada solo se ve como "Running migrations.".
     */
    private function logMigrations(): void
    {
        if (! $this->app->runningInConsole() || $this->app->environment('testing')) {
            return;
        }

        $started = [];
        Event::listen(MigrationStarted::class, function (MigrationStarted $event) use (&$started) {
            $started[$event->name] = microtime(true);
            fwrite(STDOUT, '['.now()->utc()->format('H:i:s')."] migrando: {$event->name}".PHP_EOL);
        });
        Event::listen(MigrationEnded::class, function (MigrationEnded $event) use (&$started) {
            $seconds = round(microtime(true) - ($started[$event->name] ?? microtime(true)), 1);
            fwrite(STDOUT, '['.now()->utc()->format('H:i:s')."] migrada: {$event->name} ({$seconds} s)".PHP_EOL);
        });
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

        // Recuperar contraseña: por IP, 5 por minuto y 20 por hora para pedir el enlace; 10 por minuto para usarlo
        RateLimiter::for('api-password-forgot', fn (Request $request) => [
            Limit::perMinute(5)->by('m:'.$request->ip()),
            Limit::perHour(20)->by('h:'.$request->ip()),
        ]);
        RateLimiter::for('api-password-reset', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Ficha en PDF: generar un PDF cuesta más que una consulta; 10 por minuto y 60 por hora por IP
        RateLimiter::for('api-datasheet', fn (Request $request) => [
            Limit::perMinute(10)->by('m:'.$request->ip()),
            Limit::perHour(60)->by('h:'.$request->ip()),
        ]);

        // Boletín: por IP, 5 por minuto y 20 por hora
        RateLimiter::for('api-newsletter', fn (Request $request) => [
            Limit::perMinute(5)->by('m:'.$request->ip()),
            Limit::perHour(20)->by('h:'.$request->ip()),
        ]);

        // Cotización sin cuenta: por IP, 5 por minuto y 20 por hora
        RateLimiter::for('api-quote', fn (Request $request) => [
            Limit::perMinute(5)->by('m:'.$request->ip()),
            Limit::perHour(20)->by('h:'.$request->ip()),
        ]);
    }
}
