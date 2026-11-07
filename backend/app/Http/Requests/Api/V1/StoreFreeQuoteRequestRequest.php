<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

/**
 * POST /quote-requests/free: cotización libre (sin elegir productos). Llega como multipart porque puede traer un adjunto.
 * `website` es el campo trampa contra bots (debe llegar vacío).
 */
class StoreFreeQuoteRequestRequest extends FormRequest
{
    public const MAX_ATTACHMENT_KB = 5120;

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
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'company' => ['nullable', 'string', 'max:160'],
            'nit' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
            'application' => ['nullable', 'string', 'max:255'],
            // File::types()->max() en lugar de 'file|mimes|max': Scribe no sabe documentar la regla 'max' sobre un archivo
            'attachment' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max(self::MAX_ATTACHMENT_KB)],
            'privacy_accepted' => ['accepted'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Escribe tu nombre.',
            'phone.required' => 'Escribe un teléfono o WhatsApp.',
            'phone.regex' => 'Escribe un teléfono válido.',
            'address.required' => 'Escribe tu dirección.',
            'city.required' => 'Escribe tu ciudad.',
            'email.email' => 'Escribe un correo válido.',
            'message.required' => 'Cuéntanos qué productos necesitas.',
            'message.min' => 'Cuéntanos un poco más: escribe al menos 10 caracteres.',
            'attachment.mimes' => 'El archivo debe ser PDF, JPG o PNG.',
            'attachment.max' => 'El archivo pesa más de 5 MB.',
            'attachment.uploaded' => 'No pudimos subir el archivo. Revisa que pese menos de 5 MB.',
            'privacy_accepted.accepted' => 'Debes autorizar el tratamiento de tus datos (Ley 1581 de 2012).',
        ];
    }

    public function isBot(): bool
    {
        return filled($this->input('website'));
    }
}
