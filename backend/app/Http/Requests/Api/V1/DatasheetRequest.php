<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/** Ficha técnica con cotización: referencia (SKU) y cantidad. Pública: el catálogo no pide token. */
class DatasheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:60'],
            'quantity' => ['nullable', 'numeric', 'min:0.01', 'max:100000'],
        ];
    }

    public function quantityValue(): float
    {
        return (float) ($this->validated('quantity') ?? 1);
    }
}
