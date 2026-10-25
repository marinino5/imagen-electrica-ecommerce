<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Concerns\ReadsPerPage;
use App\Services\BrandService;
use Illuminate\Foundation\Http\FormRequest;

/** Listados simples del catálogo (categorías y marcas): búsqueda y paginación. */
class ListRequest extends FormRequest
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
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.BrandService::API_PER_PAGE],
        ];
    }

    public function search(): ?string
    {
        return trim((string) $this->validated('q')) ?: null;
    }

    public function perPageValue(): int
    {
        return $this->perPage(BrandService::API_PER_PAGE, BrandService::API_PER_PAGE);
    }
}
