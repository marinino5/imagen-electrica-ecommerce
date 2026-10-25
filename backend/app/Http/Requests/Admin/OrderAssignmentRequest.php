<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Que el usuario sea un comercial activo lo valida OrderService. */
class OrderAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Admin y comercial: lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['assigned_to' => ['nullable', 'integer', 'exists:users,id']];
    }
}
