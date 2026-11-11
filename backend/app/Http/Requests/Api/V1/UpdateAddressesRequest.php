<?php

namespace App\Http\Requests\Api\V1;

use App\Support\Divipola;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** PUT /me/addresses: la lista completa de direcciones de entrega guardadas (hasta 5). Una lista vacía las borra. */
class UpdateAddressesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El rol cliente lo controla el middleware de la ruta
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'addresses' => ['present', 'array', 'max:5'],
            'addresses.*.state_code' => ['required', Rule::in(array_column(Divipola::departments(), 'code'))],
            'addresses.*.city_code' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                $index = explode('.', $attribute)[1];
                if (! Divipola::city((string) $this->input("addresses.{$index}.state_code"), (string) $value)) {
                    $fail('El municipio no pertenece al departamento elegido.');
                }
            }],
            'addresses.*.neighborhood' => ['required', 'string', 'max:100'],
            'addresses.*.road' => ['required', 'string', 'max:120'],
            'addresses.*.number' => ['required', 'string', 'max:40'],
            'addresses.*.complement' => ['nullable', 'string', 'max:120'],
            'addresses.*.postal_code' => ['nullable', 'regex:/^[A-Za-z0-9]{1,10}$/'],
            'addresses.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['addresses.max' => 'Puedes guardar hasta 5 direcciones.'];
    }
}
