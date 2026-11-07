<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/** POST /newsletter: suscripción desde el pie. `website` es el campo trampa contra bots (debe llegar vacío). */
class StoreNewsletterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255'],
            'privacy_accepted' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => 'Escribe tu correo.',
            'email.email' => 'Escribe un correo válido.',
            'privacy_accepted.accepted' => 'Debes autorizar el tratamiento de tus datos para suscribirte.',
        ];
    }

    public function isBot(): bool
    {
        return filled($this->input('website'));
    }
}
