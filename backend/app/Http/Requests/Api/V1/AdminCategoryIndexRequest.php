<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Concerns\ReadsPerPage;
use App\Services\CategoryService;
use Illuminate\Foundation\Http\FormRequest;

/** Filtros del listado de categorías del administrador: búsqueda y paginación. */
class AdminCategoryIndexRequest extends FormRequest
{
    use ReadsPerPage;

    public function authorize(): bool
    {
        return true; // El acceso (solo admin) lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.CategoryService::API_PER_PAGE],
        ];
    }

    public function search(): ?string
    {
        return trim((string) $this->validated('q')) ?: null;
    }

    public function perPageValue(): int
    {
        return $this->perPage(CategoryService::PER_PAGE, CategoryService::API_PER_PAGE);
    }
}
