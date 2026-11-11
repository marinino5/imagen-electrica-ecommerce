<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CustomerType;
use Illuminate\Foundation\Http\FormRequest;

/** PUT /me: el cliente actualiza su contacto y, si es empresa, su razón social (no el tipo, el NIT ni el correo). */
class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El rol cliente lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            // Heredados de la tienda anterior (la nueva los edita en «Datos de facturación»)
            'company_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
