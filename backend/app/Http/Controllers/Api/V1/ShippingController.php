<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ShippingService;
use App\Support\Divipola;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Compra
 */
class ShippingController extends Controller
{
    public function __construct(private readonly ShippingService $shipping) {}

    /**
     * Cotizar el envío
     *
     * Tipo de envío (`local` o `national`), zona y valor según el municipio de entrega (tabla de zonas de `config/shop.php`). Gratis al llegar
     * a la compra mínima. Sirve para mostrar el costo en el paso de entrega antes de confirmar.
     *
     * @unauthenticated
     *
     * @queryParam city_code string required Código DANE del municipio (5 dígitos). Example: 68276
     * @queryParam subtotal number Valor de los productos con IVA (para el envío gratis). Example: 119000
     *
     * @response 200 {"data":{"zone":"local","zone_label":"Bucaramanga y área metropolitana","method":"local","cost":15000,"base_cost":15000,"free":false,"free_from":500000}}
     */
    public function quote(Request $request): JsonResponse
    {
        $data = $request->validate([
            'city_code' => ['required', 'regex:/^\d{5}$/', fn (string $a, mixed $v, \Closure $fail) => Divipola::city(substr((string) $v, 0, 2), (string) $v) ? null : $fail('Ese municipio no existe.')],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
        ]);

        return response()->json(['data' => $this->shipping->quote($data['city_code'], (float) ($data['subtotal'] ?? 0))]);
    }
}
