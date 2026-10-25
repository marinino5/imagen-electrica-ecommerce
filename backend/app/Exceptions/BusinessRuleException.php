<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Se lanza cuando una operación rompe una regla de negocio (transición de
 * estado no permitida, falta de stock, borrar una categoría con productos...).
 * El mensaje siempre está en español y es seguro mostrarlo al usuario.
 *
 * Laravel llama a render() solo: la API recibe un 422 con el mensaje y el
 * panel vuelve a la página anterior con el aviso rojo.
 */
class BusinessRuleException extends RuntimeException
{
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->with('error', $this->getMessage());
    }
}
