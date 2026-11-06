<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Concerns\ReadsPerPage;
use App\Services\ProductLineService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LineCatalogRequest extends FormRequest
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
            'family' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'mode' => ['nullable', Rule::in(['direct', 'quote'])],
            'in_stock' => ['nullable', 'boolean'],
            'hp_min' => ['nullable', 'numeric', 'min:0', 'max:5000'],
            'hp_max' => ['nullable', 'numeric', 'min:0', 'max:5000', 'gte:hp_min'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.ProductLineService::MAX_PER_PAGE],
        ];
    }

    /** @return array<string, mixed> */
    public function filters(): array
    {
        return [
            'q' => trim((string) $this->validated('q')) ?: null,
            'family' => $this->validated('family'),
            'category' => $this->validated('category'),
            'brand' => $this->validated('brand'),
            'mode' => $this->validated('mode'),
            'in_stock' => $this->boolean('in_stock'),
            'hp_min' => $this->filled('hp_min') ? (float) $this->validated('hp_min') : null,
            'hp_max' => $this->filled('hp_max') ? (float) $this->validated('hp_max') : null,
        ];
    }

    public function perPageValue(): int
    {
        return $this->perPage(ProductLineService::CATALOG_PER_PAGE, ProductLineService::MAX_PER_PAGE);
    }
}
