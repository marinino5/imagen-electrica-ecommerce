<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        // A dónde va un visitante sin sesión (auth) y un usuario que ya inició sesión (guest)
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Errores de la API en JSON y en español: {"message": "..."} (y "errors" en 422)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                $default = 'Unauthenticated.';

                return response()->json([
                    'message' => $e->getMessage() === $default ? 'No has iniciado sesión o tu token no es válido.' : $e->getMessage(),
                ], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => $e->validator->errors()->first(),
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $e->getStatusCode();
            $message = match (true) {
                $status === 404 => 'No se encontró lo que buscas.',
                $status === 405 => 'Método no permitido para esta ruta.',
                $status === 429 => 'Demasiados intentos. Espera un momento antes de intentar de nuevo.',
                // Los mensajes en español que ponemos nosotros (403) se conservan; el de Laravel no
                $status === 403 && $e->getMessage() !== '' && $e->getMessage() !== 'This action is unauthorized.' => $e->getMessage(),
                $status === 403 => 'No tienes permiso para hacer esto.',
                default => 'No se pudo completar la petición.',
            };

            return response()->json(['message' => $message], $status, $e->getHeaders());
        });

        // Un error inesperado no debe mostrar detalles internos (con APP_DEBUG=true sí se ven, para depurar)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') && ! config('app.debug')) {
                return response()->json(['message' => 'Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.'], 500);
            }
        });
    })->create();
