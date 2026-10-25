<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/** Registro del cliente: usuario + datos de su empresa + política de datos. */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // El correo y el NIT se guardan sin espacios sobrantes; el correo en minúscula
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'nit' => trim((string) $this->input('nit')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'company_name' => ['required', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:20', 'unique:customers,nit'],
            'phone' => ['required', 'string', 'max:30'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            // Obligatorio: sin aceptar la política de datos no hay cuenta
            'privacy_accepted' => ['required', 'accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'nit.unique' => 'Ya existe una cuenta con ese NIT.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'privacy_accepted.required' => 'Debes aceptar la política de tratamiento de datos.',
            'privacy_accepted.accepted' => 'Debes aceptar la política de tratamiento de datos.',
        ];
    }

    /** Datos para CustomerService::register (sin el campo de la política, que solo se valida). */
    public function customerData(): array
    {
        return collect($this->validated())->except('privacy_accepted')->all();
    }
}
