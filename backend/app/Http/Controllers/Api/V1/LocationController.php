<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Divipola;
use Illuminate\Http\JsonResponse;

/**
 * @group Ubicaciones
 *
 * Departamentos y municipios de Colombia con su código DANE (DIVIPOLA), para los formularios de facturación y entrega.
 */
class LocationController extends Controller
{
    /**
     * Departamentos
     *
     * Los 33 departamentos (incluye Bogotá D.C.) con su código DANE de 2 dígitos. Público.
     *
     * @unauthenticated
     *
     * @response 200 {"data":[{"code":"68","name":"Santander"}]}
     */
    public function departments(): JsonResponse
    {
        return $this->cached(['data' => Divipola::departments()]);
    }

    /**
     * Municipios de un departamento
     *
     * Con su código DANE de 5 dígitos (los 2 primeros son los del departamento). Código desconocido responde 404.
     *
     * @unauthenticated
     *
     * @urlParam code string required Código DANE del departamento. Example: 68
     *
     * @response 200 {"data":[{"code":"68001","name":"Bucaramanga"}]}
     */
    public function municipalities(string $code): JsonResponse
    {
        abort_if(Divipola::department($code) === null, 404, 'No existe ese departamento.');

        return $this->cached(['data' => Divipola::cities($code)]);
    }

    /** Es una lista fija: el navegador y Cloudflare la pueden guardar un día. */
    private function cached(array $payload): JsonResponse
    {
        return response()->json($payload)->header('Cache-Control', 'public, max-age=86400');
    }
}
