<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Crear y editar promociones: un descuento (%) o un precio especial, con vigencia en fechas. */
class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El acceso (solo admin) lo controla el middleware de la ruta
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'discount_percent' => $this->filled('discount_percent') ? $this->input('discount_percent') : null,
            'special_price' => $this->filled('special_price') ? $this->input('special_price') : null,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'product_line_id' => ['required', 'integer', 'exists:product_lines,id'],
            'sku' => ['nullable', 'string', 'max:60'],
            'discount_percent' => ['nullable', 'numeric', 'gt:0', 'max:90'],
            'special_price' => ['nullable', 'numeric', 'gt:0', 'max:9999999999'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $hasPercent = $this->filled('discount_percent');
            $hasPrice = $this->filled('special_price');

            if ($hasPercent === $hasPrice) {
                $validator->errors()->add('discount_percent', 'Indica un porcentaje de descuento o un precio especial (uno de los dos).');
            }

            $sku = trim((string) $this->input('sku'));
            if ($sku !== '' && ! $validator->errors()->has('product_line_id')) {
                $belongs = Product::query()->where('sku', $sku)->where('line_id', $this->input('product_line_id'))->exists();
                if (! $belongs) {
                    $validator->errors()->add('sku', 'Ese SKU no existe en la línea elegida.');
                }
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Escribe el título de la promoción.',
            'product_line_id.required' => 'Elige la línea de la promoción.',
            'product_line_id.exists' => 'La línea elegida no existe.',
            'discount_percent.gt' => 'El descuento debe ser mayor que 0.',
            'discount_percent.max' => 'El descuento no puede pasar de 90 %.',
            'special_price.gt' => 'El precio especial debe ser mayor que 0.',
            'starts_at.required' => 'Indica desde cuándo rige.',
            'ends_at.required' => 'Indica hasta cuándo rige.',
            'ends_at.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
        ];
    }
}
