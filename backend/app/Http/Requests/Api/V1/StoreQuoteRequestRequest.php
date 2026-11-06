<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/** POST /quote-requests: cotización sin cuenta. `website` es el campo trampa contra bots (debe llegar vacío). */
class StoreQuoteRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\s.-]{7,30}$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'company' => ['nullable', 'string', 'max:160'],
            'nit' => ['nullable', 'string', 'max:30'],
            'message' => ['nullable', 'string', 'max:2000'],
            'privacy_accepted' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.line' => ['nullable', 'string', 'max:255', 'exists:product_lines,slug'],
            'items.*.quantity' => ['required', 'numeric', 'min:1', 'max:99999'],
        ];
    }

    public function withValidator($validator): void
    {
        // Cada renglón debe apuntar a una referencia o a una línea
        $validator->after(function ($validator) {
            foreach ((array) $this->input('items') as $i => $item) {
                if (empty($item['product_id']) && empty($item['line'])) {
                    $validator->errors()->add("items.$i", 'Cada producto debe indicar su referencia o su línea.');
                }
            }
        });
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Escribe tu nombre.',
            'phone.required' => 'Escribe un teléfono o WhatsApp.',
            'phone.regex' => 'Escribe un teléfono válido.',
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Escribe un correo válido.',
            'city.required' => 'Escribe tu ciudad.',
            'privacy_accepted.accepted' => 'Debes aceptar la política de tratamiento de datos.',
            'items.required' => 'Agrega al menos un producto a tu cotización.',
            'items.min' => 'Agrega al menos un producto a tu cotización.',
        ];
    }

    public function isBot(): bool
    {
        return filled($this->input('website'));
    }
}
