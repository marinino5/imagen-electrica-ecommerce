<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class OrderPricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin y comercial: lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'prices' => ['required', 'array'],
            // Precio unitario con IVA incluido; vacío = todavía sin valorar
            'prices.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'shipping' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'prices.*.numeric' => 'Cada precio debe ser un número.',
            'prices.*.min' => 'Los precios no pueden ser negativos.',
            'shipping.numeric' => 'El envío debe ser un número.',
            'shipping.min' => 'El envío no puede ser negativo.',
        ];
    }
}
