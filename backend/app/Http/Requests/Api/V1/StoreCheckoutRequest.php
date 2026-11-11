<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CustomerType;
use App\Enums\DeliveryMethod;
use App\Enums\DocumentType;
use App\Enums\FiscalResponsibility;
use App\Support\Divipola;
use App\Support\Nit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /checkout: la compra con los datos de facturación electrónica (DIAN, compatibles con Siigo) y la dirección de entrega.
 * Sirve igual para un invitado que para quien tiene cuenta (con token: sus datos quedan guardados en el perfil).
 *
 * - person_type = natural: tipo y número de documento, nombres y apellidos.
 * - person_type = company: NIT (el tipo de documento es NIT) con su dígito de verificación (se valida), razón social,
 *   responsabilidad fiscal y persona de contacto.
 * `website` es el campo trampa contra bots (debe llegar vacío).
 */
class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Es pública: sin token es un invitado
    }

    protected function prepareForValidation(): void
    {
        $company = $this->input('person_type') === CustomerType::Company->value;
        $docType = $company ? DocumentType::NIT->value : strtoupper(trim((string) $this->input('doc_type')));
        $number = trim((string) $this->input('doc_number'));
        $dv = trim((string) $this->input('nit_dv'));

        // «900.123.456-8» pegado entero en el campo del NIT: se separa el dígito de verificación
        if ($company && $dv === '' && preg_match('/^([\d.\s]+)-\s*(\d)$/', $number, $m)) {
            $number = $m[1];
            $dv = $m[2];
        }

        // Los documentos numéricos se guardan sin puntos, espacios ni guiones; el pasaporte en mayúscula
        $number = $docType === DocumentType::PP->value ? strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $number) ?? '') : Nit::digits($number);

        $this->merge([
            'doc_type' => $docType ?: null,
            'doc_number' => $number,
            'nit_dv' => $company ? $dv : null,
            'billing_email' => mb_strtolower(trim((string) $this->input('billing_email'))),
            'phone' => preg_replace('/[^\d+]/', '', (string) $this->input('phone')),
            'delivery_same_as_billing' => filter_var($this->input('delivery_same_as_billing'), FILTER_VALIDATE_BOOL),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $company = $this->input('person_type') === CustomerType::Company->value;
        $delivers = $this->input('delivery_method') !== DeliveryMethod::Pickup->value;
        $ownAddress = $delivers && ! $this->boolean('delivery_same_as_billing');

        $department = fn (string $field) => [
            Rule::in(array_column(Divipola::departments(), 'code')),
        ];

        return [
            ...StoreOrderRequest::itemRules(),
            'person_type' => ['required', Rule::enum(CustomerType::class)],
            'doc_type' => [$company ? 'nullable' : 'required', Rule::enum(DocumentType::class)],
            'doc_number' => ['required', 'string', $this->input('doc_type') === DocumentType::PP->value ? 'regex:/^[A-Z0-9]{5,16}$/' : 'regex:/^\d{5,12}$/'],
            'nit_dv' => [$company ? 'required' : 'nullable', 'digits:1'],
            'first_name' => [$company ? 'nullable' : 'required', 'string', 'max:80'],
            'last_name' => [$company ? 'nullable' : 'required', 'string', 'max:80'],
            'company_name' => [$company ? 'required' : 'nullable', 'string', 'max:160'],
            'fiscal_responsibility' => [$company ? 'required' : 'nullable', Rule::enum(FiscalResponsibility::class)],
            'contact_name' => [$company ? 'required' : 'nullable', 'string', 'max:120'],
            'billing_email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['required', 'regex:/^(\+57)?3\d{9}$/'],
            'billing_address' => ['required', 'string', 'max:255'],
            'department_code' => ['required', ...$department('department_code')],
            'city_code' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if (! Divipola::city((string) $this->input('department_code'), (string) $value)) {
                    $fail('El municipio no pertenece al departamento elegido.');
                }
            }],

            'delivery_method' => ['required', Rule::enum(DeliveryMethod::class)],
            'delivery_same_as_billing' => ['boolean'],
            'delivery_department_code' => [Rule::requiredIf($ownAddress), 'nullable', ...$department('delivery_department_code')],
            'delivery_city_code' => [Rule::requiredIf($ownAddress), 'nullable', 'string', function (string $attribute, mixed $value, \Closure $fail) use ($ownAddress) {
                if ($ownAddress && ! Divipola::city((string) $this->input('delivery_department_code'), (string) $value)) {
                    $fail('El municipio de entrega no pertenece al departamento elegido.');
                }
            }],
            'delivery_neighborhood' => [Rule::requiredIf($ownAddress), 'nullable', 'string', 'max:100'],
            'delivery_road' => [Rule::requiredIf($ownAddress), 'nullable', 'string', 'max:120'],
            'delivery_number' => [Rule::requiredIf($ownAddress), 'nullable', 'string', 'max:40'],
            'delivery_complement' => ['nullable', 'string', 'max:120'],
            'delivery_postal_code' => ['nullable', 'regex:/^\d{6}$/'],
            'delivery_notes' => ['nullable', 'string', 'max:500'],
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
                if ($this->input('person_type') !== CustomerType::Company->value || $validator->errors()->hasAny(['doc_number', 'nit_dv'])) {
                    return;
                }

                if (! Nit::isValid((string) $this->input('doc_number'), $this->input('nit_dv'))) {
                    $validator->errors()->add('nit_dv', 'El dígito de verificación no corresponde a ese NIT. Revísalo (el de '.$this->input('doc_number').' es '.Nit::checkDigit((string) $this->input('doc_number')).').');
                }
            },
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'person_type' => 'tipo de persona', 'doc_type' => 'tipo de documento', 'doc_number' => 'número de documento', 'nit_dv' => 'dígito de verificación',
            'first_name' => 'nombres', 'last_name' => 'apellidos', 'company_name' => 'razón social', 'fiscal_responsibility' => 'responsabilidad fiscal',
            'contact_name' => 'persona de contacto', 'billing_email' => 'correo de facturación', 'phone' => 'celular', 'billing_address' => 'dirección',
            'department_code' => 'departamento', 'city_code' => 'municipio', 'delivery_department_code' => 'departamento de entrega',
            'delivery_city_code' => 'municipio de entrega', 'delivery_neighborhood' => 'barrio', 'delivery_road' => 'vía', 'delivery_number' => 'número',
            'delivery_postal_code' => 'código postal',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return StoreOrderRequest::itemMessages() + [
            'doc_number.regex' => 'El número de documento no es válido: usa solo números (el pasaporte puede llevar letras).',
            'nit_dv.required' => 'Escribe el dígito de verificación del NIT.',
            'nit_dv.digits' => 'El dígito de verificación es un solo número.',
            'phone.regex' => 'Escribe un celular colombiano de 10 dígitos (empieza por 3).',
            'billing_email.email' => 'Escribe un correo válido.',
            'delivery_postal_code.regex' => 'El código postal tiene 6 números.',
            'privacy_accepted.accepted' => 'Debes autorizar el tratamiento de tus datos personales (Ley 1581 de 2012).',
        ];
    }

    public function isBot(): bool
    {
        return filled($this->input('website'));
    }
}
