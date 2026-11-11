<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\FiscalCode;
use App\Enums\IdType;
use App\Support\Divipola;
use App\Support\Nit;
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
    public function authorize(): bool
    {
        return true; // Es pública: sin token es un invitado
    }

    protected function prepareForValidation(): void
    {
        $invoice = $this->boolean('needs_invoice');
        $personType = match (strtolower((string) $this->input('person_type'))) {
            'company', 'empresa' => 'Company',
            'person', 'natural' => 'Person',
            default => $this->input('person_type'),
        };
        $idType = $personType === 'Company' ? IdType::NIT->value : (string) $this->input('id_type');
        $number = trim((string) $this->input('identification'));
        $dv = trim((string) $this->input('check_digit'));

        // «900.123.456-8» pegado entero en el campo del NIT: se separa el dígito de verificación
        if ($personType === 'Company' && $dv === '' && preg_match('/^([\d.\s]+)-\s*(\d)$/', $number, $m)) {
            $number = $m[1];
            $dv = $m[2];
        }

        $numeric = in_array($idType, [IdType::CC->value, IdType::NIT->value], true);
        // Se quitan puntos, espacios y guiones; las letras en una cédula o un NIT NO se descartan: la validación las rechaza
        $number = strtoupper(preg_replace($numeric ? '/[\s.\-]+/' : '/[^A-Za-z0-9]/', '', $number) ?? '');

        $this->merge([
            'needs_invoice' => $invoice,
            'person_type' => $personType,
            'id_type' => $idType !== '' ? $idType : null,
            'identification' => $number,
            'check_digit' => $personType === 'Company' ? $dv : null,
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'billing_email' => mb_strtolower(trim((string) ($this->input('billing_email') ?: $this->input('email')))),
            'phone' => preg_replace('/[^\d+]/', '', (string) $this->input('phone')),
            'billing_same_as_delivery' => filter_var($this->input('billing_same_as_delivery'), FILTER_VALIDATE_BOOL),
            'vat_responsible' => filter_var($this->input('vat_responsible'), FILTER_VALIDATE_BOOL),
            'fiscal_responsibilities' => $this->input('fiscal_responsibilities') ?: [FiscalCode::NotApplicable->value],
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $delivers = $this->input('delivery_method') !== 'pickup';
        $invoice = $this->boolean('needs_invoice');
        $company = $this->input('person_type') === 'Company';
        $ownAddress = $invoice && ! ($delivers && $this->boolean('billing_same_as_delivery'));
        $numeric = in_array($this->input('id_type'), [IdType::CC->value, IdType::NIT->value], true);

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
            'needs_invoice' => ['boolean'],
            'person_type' => [Rule::requiredIf($invoice), 'nullable', Rule::in(['Person', 'Company'])],
            'id_type' => [Rule::requiredIf($invoice && ! $company), 'nullable', Rule::enum(IdType::class)],
            'identification' => [Rule::requiredIf($invoice), 'nullable', 'string', $numeric ? 'regex:/^\d{3,13}$/' : 'regex:/^[A-Z0-9]{1,20}$/'],
            'check_digit' => [Rule::requiredIf($invoice && $company), 'nullable', 'digits:1'],
            'first_name' => [Rule::requiredIf($invoice && ! $company), 'nullable', 'string', 'max:100'],
            'last_name' => [Rule::requiredIf($invoice && ! $company), 'nullable', 'string', 'max:100'],
            'company_name' => [Rule::requiredIf($invoice && $company), 'nullable', 'string', 'max:100'],
            'commercial_name' => ['nullable', 'string', 'max:100'],
            'vat_responsible' => ['boolean'],
            'fiscal_responsibilities' => ['array', 'min:1'],
            'fiscal_responsibilities.*' => [Rule::in(FiscalCode::values())],
            'billing_email' => ['nullable', 'email:rfc', 'max:100'],
            'billing_same_as_delivery' => ['boolean'],
            'billing_address' => [Rule::requiredIf($ownAddress), 'nullable', 'string', 'max:256'],
            'billing_state_code' => [Rule::requiredIf($ownAddress), 'nullable', ...$stateRule],
            'billing_city_code' => [Rule::requiredIf($ownAddress), 'nullable', 'string', $cityRule('billing_state_code', 'El municipio de facturación no pertenece al departamento elegido.')],
            'billing_postal_code' => ['nullable', 'regex:/^[A-Za-z0-9]{1,10}$/'],
            'contact_first_name' => ['nullable', 'string', 'max:50'],
            'contact_last_name' => ['nullable', 'string', 'max:50'],

            'notes' => ['nullable', 'string', 'max:1000'],
            'privacy_accepted' => ['accepted'],
            'save_to_profile' => ['nullable', 'boolean'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** El dígito de verificación del NIT tiene que ser el que sale del número (cálculo de la DIAN). */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if (! $this->boolean('needs_invoice') || $this->input('person_type') !== 'Company' || $validator->errors()->hasAny(['identification', 'check_digit'])) {
                    return;
                }

                if (! Nit::isValid((string) $this->input('identification'), $this->input('check_digit'))) {
                    $validator->errors()->add('check_digit', 'El dígito de verificación no corresponde a ese NIT. Revísalo (el de '.$this->input('identification').' es '.Nit::checkDigit((string) $this->input('identification')).').');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'delivery_method' => 'forma de entrega', 'delivery_state_code' => 'departamento de entrega', 'delivery_city_code' => 'municipio de entrega',
            'delivery_neighborhood' => 'barrio', 'delivery_road' => 'vía', 'delivery_number' => 'número', 'delivery_postal_code' => 'código postal',
            'name' => 'nombre', 'phone' => 'celular', 'email' => 'correo',
            'person_type' => 'tipo de persona', 'id_type' => 'tipo de documento', 'identification' => 'número de documento', 'check_digit' => 'dígito de verificación',
            'first_name' => 'nombres', 'last_name' => 'apellidos', 'company_name' => 'razón social', 'fiscal_responsibilities' => 'responsabilidad fiscal',
            'billing_email' => 'correo de facturación', 'billing_address' => 'dirección de facturación', 'billing_state_code' => 'departamento de facturación',
            'billing_city_code' => 'municipio de facturación', 'billing_postal_code' => 'código postal de facturación',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return StoreOrderRequest::itemMessages() + [
            'identification.regex' => 'El número de documento no es válido: usa solo números (CE y pasaporte pueden llevar letras).',
            'check_digit.required' => 'Escribe el dígito de verificación del NIT.',
            'check_digit.digits' => 'El dígito de verificación es un solo número.',
            'phone.regex' => 'Escribe un celular colombiano de 10 dígitos (empieza por 3).',
            'email.email' => 'Escribe un correo válido.',
            'billing_email.email' => 'Escribe un correo de facturación válido.',
            'delivery_postal_code.regex' => 'El código postal solo lleva letras y números (hasta 10).',
            'privacy_accepted.accepted' => 'Debes autorizar el tratamiento de tus datos personales (Ley 1581 de 2012).',
        ];
    }

    public function isBot(): bool
    {
        return filled($this->input('website'));
    }
}
