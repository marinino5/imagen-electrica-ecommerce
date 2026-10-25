<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Crear y editar productos (en edición, el producto viene de la ruta). */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El acceso (solo admin) lo controla el middleware de la ruta
    }

    protected function prepareForValidation(): void
    {
        // Las filas de especificaciones totalmente vacías no se validan
        $specs = collect($this->input('specs', []))
            ->filter(fn ($row) => is_array($row) && (filled($row['key'] ?? null) || filled($row['value'] ?? null)))
            ->values()
            ->all();

        $this->merge([
            'specs' => $specs,
            'is_active' => $this->boolean('is_active'),
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            // La tabla completa (también eliminados) exige SKU único
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'unit' => ['required', Rule::enum(ProductUnit::class)],
            'sale_mode' => ['required', Rule::enum(SaleMode::class)],
            // Sin precio el producto solo se vende por cotización
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', Rule::requiredIf($this->input('sale_mode') === SaleMode::Direct->value)],
            // Costo interno sin IVA, para los reportes (solo lo editan los admin, dueños de esta ruta)
            'cost_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'specs' => ['array', 'max:30'],
            'specs.*.key' => ['required', 'string', 'max:60', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
            'specs.*.value' => ['required', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'price.required' => 'Un producto de venta directa necesita precio.',
            'specs.*.key.required' => 'Cada especificación necesita un nombre.',
            'specs.*.key.regex' => 'El nombre de la especificación va en minúsculas, sin espacios ni tildes, con la unidad al final (ej. potencia_hp).',
            'specs.*.key.distinct' => 'Hay nombres de especificación repetidos.',
            'specs.*.value.required' => 'Cada especificación necesita un valor.',
        ];
    }
}
