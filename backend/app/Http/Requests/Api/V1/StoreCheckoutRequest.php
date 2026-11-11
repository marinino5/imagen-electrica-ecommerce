<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Concerns\InvoiceFields;
use App\Support\Divipola;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /checkout. Cuatro pasos en la tienda: 1 entrega · 2 tus datos · 3 facturación electrónica (opcional) · 4 confirmar.
 *
 * - Entrega: `pickup` (recoger en tienda, sin dirección) o `delivery` (domicilio: departamento, municipio DANE, barrio, vía, número…).
 *   El servidor decide «local» o «nacional» por el municipio (ver ShippingService).
 * - Tus datos: `name`, `phone` (celular) y `email`: lo mínimo para contactar y enviar el resumen.
 * - Factura (`needs_invoice`): los campos de la API de clientes de Siigo Nube (developers.siigo.com), con sus nombres y códigos:
 *   person_type (Person | Company), id_type (13 CC, 31 NIT, 22 CE, 41 pasaporte), identification, check_digit (solo NIT, se valida),
 *   nombres y apellidos o razón social, commercial_name, vat_responsible, fiscal_responsibilities (R-99-PN, O-13, O-15, O-23, O-47),
 *   dirección con state_code y city_code (DANE) y postal_code.
 * Sin factura no se pide ningún documento. `website` es el campo trampa contra bots (debe llegar vacío).
 */
class StoreCheckoutRequest extends FormRequest
{
    use InvoiceFields;

    public function authorize(): bool
    {
        return true; // Es pública: sin token es un invitado
    }

    protected function prepareForValidation(): void
    {
        $this->prepareInvoice();
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'phone' => preg_replace('/[^\d+]/', '', (string) $this->input('phone')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $delivers = $this->input('delivery_method') !== 'pickup';
        $stateRule = [Rule::in(array_column(Divipola::departments(), 'code'))];
        $cityRule = fn (string $stateField, string $message) => function (string $attribute, mixed $value, \Closure $fail) use ($stateField, $message) {
            if (! Divipola::city((string) $this->input($stateField), (string) $value)) {
                $fail($message);
            }
        };

        return [
            ...StoreOrderRequest::itemRules(),

            // 1 · Entrega
            'delivery_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'delivery_state_code' => [Rule::requiredIf($delivers), 'nullable', ...$stateRule],
            'delivery_city_code' => [Rule::requiredIf($delivers), 'nullable', 'string', $cityRule('delivery_state_code', 'El municipio de entrega no pertenece al departamento elegido.')],
            'delivery_neighborhood' => [Rule::requiredIf($delivers), 'nullable', 'string', 'max:100'],
            'delivery_road' => [Rule::requiredIf($delivers), 'nullable', 'string', 'max:120'],
            'delivery_number' => [Rule::requiredIf($delivers), 'nullable', 'string', 'max:40'],
            'delivery_complement' => ['nullable', 'string', 'max:120'],
            'delivery_postal_code' => ['nullable', 'regex:/^[A-Za-z0-9]{1,10}$/'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],

            // 2 · Tus datos
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'regex:/^(\+57)?3\d{9}$/'],
            'email' => ['required', 'email:rfc', 'max:255'],

            // 3 · Facturación electrónica (nombres de la API de clientes de Siigo)
            ...$this->invoiceRules($delivers && $this->boolean('billing_same_as_delivery')),

            'notes' => ['nullable', 'string', 'max:1000'],
            'privacy_accepted' => ['accepted'],
            'save_to_profile' => ['nullable', 'boolean'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateNitCheckDigit($validator)];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'delivery_method' => 'forma de entrega', 'delivery_state_code' => 'departamento de entrega', 'delivery_city_code' => 'municipio de entrega',
            'delivery_neighborhood' => 'barrio', 'delivery_road' => 'vía', 'delivery_number' => 'número', 'delivery_postal_code' => 'código postal',
            'name' => 'nombre', 'phone' => 'celular', 'email' => 'correo',
        ] + $this->invoiceAttributes();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return StoreOrderRequest::itemMessages() + $this->invoiceMessages() + [
            'phone.regex' => 'Escribe un celular colombiano de 10 dígitos (empieza por 3).',
            'email.email' => 'Escribe un correo válido.',
            'delivery_postal_code.regex' => 'El código postal solo lleva letras y números (hasta 10).',
            'privacy_accepted.accepted' => 'Debes autorizar el tratamiento de tus datos personales (Ley 1581 de 2012).',
        ];
    }

    public function isBot(): bool
    {
        return filled($this->input('website'));
    }
}
