<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Admin\ProductIndexRequest;
use App\Http\Requests\Concerns\ReadsPerPage;
use App\Services\ProductService;

/** Listado de productos del administrador: los filtros del panel más per_page. */
class AdminProductIndexRequest extends ProductIndexRequest
{
    use ReadsPerPage;

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return parent::rules() + ['per_page' => ['nullable', 'integer', 'min:1', 'max:'.ProductService::MAX_PER_PAGE]];
    }

    public function perPageValue(): int
    {
        return $this->perPage(ProductService::PER_PAGE, ProductService::MAX_PER_PAGE);
    }
}
