<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CatalogRequest;
use App\Http\Resources\V1\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Catálogo (público)
 */
class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    /**
     * Listar productos
     *
     * Productos activos con búsqueda por nombre o referencia, filtros, orden y
     * paginación. Los precios incluyen IVA; `price` en `null` significa que el producto
     * solo se vende por cotización. No requiere token.
     *
     * @unauthenticated
     *
     * @queryParam category string Slug de la categoría (incluye sus subcategorías). Example: motores
     * @queryParam brand string Slug de la marca. Example: siemens
     * @queryParam sort string Orden: name (por defecto), newest, price_asc, price_desc. Los productos sin precio quedan al final. Example: price_asc
     *
     * @apiResourceCollection App\Http\Resources\V1\ProductResource
     *
     * @apiResourceModel App\Models\Product with=category,brand paginate=12
     */
    public function index(CatalogRequest $request): AnonymousResourceCollection
    {
        return ProductResource::collection($this->products->catalog($request->filters(), $request->perPageValue()));
    }

    /**
     * Ver un producto
     *
     * Detalle de un producto activo por su slug. Si no existe o está inactivo responde 404.
     *
     * @unauthenticated
     *
     * @urlParam slug string required Slug del producto. Example: motor-trifasico-5-hp
     *
     * @apiResource App\Http\Resources\V1\ProductResource
     *
     * @apiResourceModel App\Models\Product with=category,brand
     */
    public function show(string $slug): ProductResource
    {
        return new ProductResource($this->products->findActiveBySlug($slug));
    }
}
