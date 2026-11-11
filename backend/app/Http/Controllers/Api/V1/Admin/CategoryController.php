<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminCategoryIndexRequest;
use App\Http\Requests\Api\V1\CategoryRequest;
use App\Http\Resources\V1\CategoryAdminResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @group Categorías (administrador)
 *
 * Requiere el token de un usuario con rol admin; con otro rol responde 403.
 *
 * @authenticated
 */
class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    /**
     * Listar categorías
     *
     * A diferencia del catálogo público, incluye las categorías inactivas.
     *
     * @queryParam q string Busca por nombre o slug. Example: motores
     * @queryParam per_page integer Categorías por página (1 a 50). Example: 10
     *
     * @apiResourceCollection App\Http\Resources\V1\CategoryAdminResource
     *
     * @apiResourceModel App\Models\Category paginate=10
     */
    public function index(AdminCategoryIndexRequest $request): AnonymousResourceCollection
    {
        return CategoryAdminResource::collection($this->categories->paginate($request->search(), $request->perPageValue()));
    }

    /**
     * Crear una categoría
     *
     * El slug se genera a partir del nombre. Solo hay dos niveles: `parent_id` debe ser una categoría de primer nivel.
     *
     * @bodyParam parent_id integer Categoría padre (de primer nivel). Example: 1
     * @bodyParam name string required Example: Motores eléctricos
     * @bodyParam description string Example: Motores trifásicos y monofásicos.
     * @bodyParam sort_order integer Orden en el menú (0 por defecto). Example: 2
     * @bodyParam is_active boolean Activa en la tienda (por defecto sí). Example: true
     *
     * @apiResource App\Http\Resources\V1\CategoryAdminResource status=201
     *
     * @apiResourceModel App\Models\Category
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->categories->create($request->validated());

        // fresh() relee la fila para traer los valores por defecto de la base (is_active)
        return (new CategoryAdminResource($category->fresh()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Ver una categoría
     *
     * @urlParam category integer required Id de la categoría. Example: 1
     *
     * @apiResource App\Http\Resources\V1\CategoryAdminResource
     *
     * @apiResourceModel App\Models\Category
     */
    public function show(Category $category): CategoryAdminResource
    {
        return new CategoryAdminResource($category);
    }

    /**
     * Actualizar una categoría
     *
     * Reemplaza la categoría: se envían los mismos campos obligatorios que al crear. El slug no cambia.
     *
     * @urlParam category integer required Id de la categoría. Example: 1
     *
     * @bodyParam parent_id integer Example: 1
     * @bodyParam name string required Example: Motores eléctricos industriales
     * @bodyParam description string Example: Motores trifásicos y monofásicos.
     * @bodyParam sort_order integer Example: 3
     * @bodyParam is_active boolean Si no se envía, se conserva. Example: true
     *
     * @apiResource App\Http\Resources\V1\CategoryAdminResource
     *
     * @apiResourceModel App\Models\Category
     */
    public function update(CategoryRequest $request, Category $category): CategoryAdminResource
    {
        return new CategoryAdminResource($this->categories->update($category, $request->validated())->fresh());
    }

    /**
     * Eliminar una categoría
     *
     * Si la categoría tiene productos o subcategorías responde 422: primero hay que moverlos.
     *
     * @urlParam category integer required Id de la categoría. Example: 1
     *
     * @response 204 scenario="Eliminada" {}
     * @response 422 scenario="Tiene productos" {"message": "No se puede eliminar «Motores» porque tiene 3 productos. Muévelos a otra categoría primero."}
     */
    public function destroy(Category $category): Response
    {
        $this->categories->delete($category);

        return response()->noContent();
    }
}
