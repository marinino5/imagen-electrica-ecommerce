<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListRequest;
use App\Http\Resources\V1\BrandResource;
use App\Services\BrandService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Catálogo (público)
 */
class BrandController extends Controller
{
    public function __construct(private readonly BrandService $brands) {}

    /**
     * Listar marcas
     *
     * Marcas activas ordenadas por nombre. No requiere token.
     *
     * @unauthenticated
     *
     * @apiResourceCollection App\Http\Resources\V1\BrandResource
     *
     * @apiResourceModel App\Models\Brand paginate=50
     */
    public function index(ListRequest $request): AnonymousResourceCollection
    {
        return BrandResource::collection($this->brands->paginateActive($request->search(), $request->perPageValue()));
    }
}
