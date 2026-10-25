<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListRequest;
use App\Http\Resources\V1\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Catálogo (público)
 */
class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    /**
     * Listar categorías
     *
     * Categorías activas en orden de árbol: cada categoría principal seguida de sus
     * subcategorías (`parent_id` indica el padre). No requiere token.
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\V1\CategoryResource
     *
     * @apiResourceModel App\Models\Category paginate=50
     */
    public function index(ListRequest $request): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->categories->paginateActive($request->search(), $request->perPageValue()));
    }
}
