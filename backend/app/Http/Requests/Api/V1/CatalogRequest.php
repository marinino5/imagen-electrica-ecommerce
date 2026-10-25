<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\SaleMode;
use App\Http\Requests\Concerns\ReadsPerPage;
use App\Services\ProductService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Filtros, búsqueda, orden y paginación del catálogo público de productos. */
class CatalogRequest extends FormRequest
{
    use ReadsPerPage;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'sale_mode' => ['nullable', Rule::enum(SaleMode::class)],
            'featured' => ['nullable', 'boolean'],
            'in_stock' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['name', 'newest', 'price_asc', 'price_desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.ProductService::MAX_PER_PAGE],
        ];
    }

    /** @return array{q: ?string, category: ?string, brand: ?string, sale_mode: ?string, featured: bool, in_stock: bool, sort: ?string} */
    public function filters(): array
    {
        return [
            'q' => trim((string) $this->validated('q')) ?: null,
            'category' => $this->validated('category'),
            'brand' => $this->validated('brand'),
            'sale_mode' => $this->validated('sale_mode'),
            'featured' => $this->boolean('featured'),
            'in_stock' => $this->boolean('in_stock'),
            'sort' => $this->validated('sort'),
        ];
    }

    public function perPageValue(): int
    {
        return $this->perPage(ProductService::CATALOG_PER_PAGE, ProductService::MAX_PER_PAGE);
    }
}
