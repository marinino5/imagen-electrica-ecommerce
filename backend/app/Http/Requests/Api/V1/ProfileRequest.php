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
        $isCompany = $this->user()?->customer?->type === CustomerType::Company;

        return [
            'name' => ['required', 'string', 'max:255'],
            // La persona natural no tiene razón social
            'company_name' => [$isCompany ? 'required' : 'nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ];
    }
}
