<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deja pasar solo a usuarios activos con alguno de los roles indicados.
 * Uso en rutas: ->middleware('role:admin') o ->middleware('role:admin,comercial').
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! in_array($user->role->value, $roles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'No tienes permiso para entrar a esta sección.');
        }

        return $next($request);
    }
}
