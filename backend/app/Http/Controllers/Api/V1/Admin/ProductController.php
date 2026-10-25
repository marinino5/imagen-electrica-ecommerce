<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminProductIndexRequest;
use App\Http\Requests\Api\V1\ProductRequest;
use App\Http\Resources\V1\ProductAdminResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @group Productos (administrador)
 *
 * Requiere el token de un usuario con rol admin; con otro rol responde 403.
 *
 * @authenticated
 */
class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    /**
     * Listar productos
     *
     * A diferencia del catálogo público, incluye productos inactivos y permite ver los eliminados.
     *
     * @queryParam q string Busca por nombre o referencia. Example: motor
     * @queryParam category_id integer Categoría (incluye subcategorías). Example: 2
     * @queryParam brand_id integer Marca. Example: 1
     * @queryParam sale_mode string direct, quote u on_request. Example: direct
     * @queryParam stock string low (stock bajo) u out (agotado). Example: low
     * @queryParam status string active, inactive o trashed (eliminados). Example: active
     * @queryParam per_page integer Productos por página (1 a 50). Example: 10
     *
     * @apiResourceCollection App\Http\Resources\V1\ProductAdminResource
     *
     * @apiResourceModel App\Models\Product with=category,brand paginate=10
     */
    public function index(AdminProductIndexRequest $request): AnonymousResourceCollection
    {
        return ProductAdminResource::collection($this->products->paginate($request->filters(), $request->perPageValue()));
    }

    /**
     * Crear un producto
     *
     * El slug se genera a partir del nombre. `specs` es un objeto con claves fijas en minúscula y la
     * unidad en el nombre. Un producto de venta directa necesita precio.
     *
     * @bodyParam category_id integer required Example: 1
     * @bodyParam brand_id integer required Example: 1
     * @bodyParam sku string required Referencia, igual al código de Siigo (única). Example: MOT-5HP-220
     * @bodyParam name string required Example: Motor trifásico 5 HP
     * @bodyParam short_description string required Example: Motor para uso industrial
     * @bodyParam description string Example: Motor de inducción jaula de ardilla.
     * @bodyParam image_url string URL pública de la imagen. Example: https://example.com/motor.jpg
     * @bodyParam specs object Especificaciones técnicas. Example: {"potencia_hp": 5, "voltaje_v": 220}
     * @bodyParam unit string required unidad, metro, rollo o caja. Example: unidad
     * @bodyParam sale_mode string required direct, quote u on_request. Example: direct
     * @bodyParam price number Precio con IVA. Obligatorio si es direct. Example: 1500000
     * @bodyParam cost_price number Costo de compra sin IVA (interno, nunca se muestra). Example: 1100000
     * @bodyParam tax_rate number required IVA en %. Example: 19
     * @bodyParam stock integer required Example: 4
     * @bodyParam is_active boolean Activo en la tienda (por defecto sí). Example: true
     * @bodyParam is_featured boolean Destacado (por defecto no). Example: false
     *
     * @apiResource App\Http\Resources\V1\ProductAdminResource status=201
     *
     * @apiResourceModel App\Models\Product with=category,brand
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        // fresh() relee la fila para traer los valores por defecto de la base (is_active, etc.)
        return (new ProductAdminResource($product->fresh(['category', 'brand'])))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Ver un producto
     *
     * @urlParam product integer required Id del producto. Example: 1
     *
     * @apiResource App\Http\Resources\V1\ProductAdminResource
     *
     * @apiResourceModel App\Models\Product with=category,brand
     */
    public function show(Product $product): ProductAdminResource
    {
        return new ProductAdminResource($product->load(['category', 'brand']));
    }

    /**
     * Actualizar un producto
     *
     * Reemplaza el producto: se envían los mismos campos obligatorios que al crear. El slug no cambia. Si no
     * se envían `is_active`, `is_featured` o `specs`, esos datos se conservan.
     *
     * @urlParam product integer required Id del producto. Example: 1
     *
     * @bodyParam category_id integer required Example: 1
     * @bodyParam brand_id integer required Example: 1
     * @bodyParam sku string required Example: MOT-5HP-220
     * @bodyParam name string required Example: Motor trifásico 5 HP
     * @bodyParam short_description string required Example: Motor para uso industrial
     * @bodyParam unit string required Example: unidad
     * @bodyParam sale_mode string required Example: direct
     * @bodyParam price number Obligatorio si es direct. Example: 1600000
     * @bodyParam tax_rate number required Example: 19
     * @bodyParam stock integer required Example: 6
     *
     * @apiResource App\Http\Resources\V1\ProductAdminResource
     *
     * @apiResourceModel App\Models\Product with=category,brand
     */
    public function update(ProductRequest $request, Product $product): ProductAdminResource
    {
        $product = $this->products->update($product, $request->validated());

        return new ProductAdminResource($product->load(['category', 'brand']));
    }

    /**
     * Eliminar un producto
     *
     * Borrado lógico: el producto desaparece de la tienda pero los pedidos antiguos conservan su copia.
     *
     * @urlParam product integer required Id del producto. Example: 1
     *
     * @response 204 scenario="Eliminado" {}
     */
    public function destroy(Product $product): Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}
