<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Admin\ProductRequest as PanelProductRequest;

/**
 * Crear y reemplazar productos desde la API (token de administrador). Mismas
 * reglas que el panel; cambian dos cosas propias de JSON:
 * - specs llega como objeto {"potencia_hp": 5} y se convierte a las filas
 *   clave-valor que ya entiende ProductService.
 * - is_active e is_featured son opcionales: si no se envían, no se tocan.
 */
class ProductRequest extends PanelProductRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('specs')) {
            return;
        }

        $specs = $this->input('specs');
        $rows = [];

        foreach (is_array($specs) ? $specs : [] as $key => $value) {
            $rows[] = ['key' => (string) $key, 'value' => is_scalar($value) ? (string) $value : $value];
        }

        $this->merge(['specs' => $rows]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'specs' => ['sometimes', 'array', 'max:30'],
        ]);
    }
}
