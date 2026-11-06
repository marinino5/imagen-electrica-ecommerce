<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LineCatalogRequest;
use App\Http\Resources\V1\FamilyResource;
use App\Http\Resources\V1\LineResource;
use App\Services\ProductLineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Catálogo por líneas (público)
 *
 * Familia → categoría → línea (la ficha) → referencias (SKU). No necesita token.
 */
class LineController extends Controller
{
    public function __construct(private readonly ProductLineService $lines) {}

    /**
     * Líneas del catálogo
     *
     * Resumen de cada línea: rango de potencia, "desde" el precio más bajo con IVA, stock total y, si tiene una
     * sola referencia comprable, esa referencia lista para agregar al carrito.
     *
     * @queryParam q string Busca por línea, categoría, familia, marca, nombre de referencia o SKU. Example: CFW300
     * @queryParam family string Slug de la familia. Example: motores-electricos
     * @queryParam category string Slug de la categoría. Example: motores-trifasicos
     * @queryParam brand string Marca. Example: WEG
     * @queryParam mode string direct (compra en línea) o quote (cotización o bajo pedido). Example: direct
     * @queryParam in_stock boolean Solo líneas con unidades en bodega. Example: 1
     * @queryParam hp_min number Potencia mínima en HP de alguna referencia. Example: 1
     * @queryParam hp_max number Potencia máxima en HP de alguna referencia. Example: 5
     * @queryParam per_page integer Líneas por página (1 a 50; por defecto 50). Example: 50
     */
    public function index(LineCatalogRequest $request): AnonymousResourceCollection
    {
        return LineResource::collection($this->lines->catalog($request->filters(), $request->perPageValue()));
    }

    /**
     * Ficha de una línea
     *
     * La línea con todas sus referencias (potencia, velocidad, corriente, tensión, protección, stock y precio con IVA)
     * y hasta 3 líneas relacionadas de la misma familia. Si no existe o está inactiva responde 404.
     *
     * @urlParam slug string required Slug de la línea. Example: w22-iec-carcasa-hierro
     */
    public function show(string $slug): JsonResponse
    {
        $line = $this->lines->findActiveBySlug($slug);

        return LineResource::detail($line)
            ->additional(['related' => LineResource::collection($this->lines->related($line))])
            ->response();
    }

    /**
     * Familias del portafolio
     *
     * Familias con sus categorías y el número de líneas de cada una, más las marcas y los totales del catálogo.
     */
    public function families(): JsonResponse
    {
        return FamilyResource::collection($this->lines->families())
            ->additional(['meta' => $this->lines->facets()])
            ->response();
    }
}
