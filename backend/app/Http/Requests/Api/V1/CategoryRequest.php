<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Admin\CategoryRequest as PanelCategoryRequest;

/**
 * Crear y reemplazar categorías desde la API (token de administrador). Mismas
 * reglas que el panel (padre de primer nivel, no ser su propio padre...); cambia
 * una cosa propia de JSON: `is_active` es opcional y, si no se envía, no se toca
 * (el panel lo manda siempre desde la casilla del formulario).
 */
class CategoryRequest extends PanelCategoryRequest
{
    protected function prepareForValidation(): void
    {
        // Sin is_active en el JSON no se fuerza a false como hace el panel
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
