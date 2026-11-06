<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PromotionResource;
use App\Services\PromotionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Catálogo por líneas (público)
 */
class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $promotions) {}

    /**
     * Promociones del momento
     *
     * Promociones activas cuya vigencia incluye hoy, de la que vence primero a la que vence después. Cada una trae
     * el precio de lista, el precio promocional (con IVA) y la línea a la que enlaza. Si no hay ninguna, `data` va vacío
     * y la tienda oculta la sección. No requiere token.
     *
     * @unauthenticated
     */
    public function index(): AnonymousResourceCollection
    {
        return PromotionResource::collection($this->promotions->current());
    }
}
