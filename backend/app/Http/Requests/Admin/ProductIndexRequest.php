<?php

namespace App\Http\Requests\Admin;

use App\Enums\SaleMode;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Filtros del listado de productos, todos aplicables desde la URL. Un valor
 * desconocido se ignora (en vez de dar error) para que un enlace viejo
 * no deje la página inutilizable.
 */
class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:100']];
    }

    /** @return array{q: ?string, category_id: ?int, brand_id: ?int, sale_mode: ?string, stock: ?string, status: ?string} */
    public function filters(): array
    {
        return [
            'q' => trim((string) $this->validated('q')) ?: null,
            'category_id' => $this->integer('category_id') ?: null,
            'brand_id' => $this->integer('brand_id') ?: null,
            'sale_mode' => $this->enum('sale_mode', SaleMode::class)?->value,
            'stock' => in_array($this->query('stock'), ['low', 'out'], true) ? $this->query('stock') : null,
            'status' => in_array($this->query('status'), ['active', 'inactive', 'trashed'], true) ? $this->query('status') : null,
        ];
    }
}
