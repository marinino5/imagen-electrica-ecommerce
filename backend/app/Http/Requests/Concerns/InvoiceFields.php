<?php

namespace App\Http\Requests\Concerns;

use App\Enums\FiscalCode;
use App\Enums\IdType;
use App\Support\Divipola;
use App\Support\Nit;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

/**
 * Los datos de facturación electrónica con los nombres de la API de clientes de Siigo Nube (developers.siigo.com): los mismos en el pago,
 * al crear la cuenta y al editar «Mis datos». Cada solicitud que los lleva usa este trait para validar igual y no duplicar reglas.
 *
 * person_type (Person | Company), id_type (13 CC, 31 NIT, 22 CE, 41 pasaporte), identification, check_digit (solo NIT, se valida),
 * first_name + last_name o company_name, commercial_name, vat_responsible, fiscal_responsibilities (R-99-PN, O-13, O-15, O-23, O-47),
 * billing_address, billing_state_code y billing_city_code (DANE), billing_postal_code, billing_email y contact_first_name / contact_last_name.
 */
trait InvoiceFields
{
    /** Normaliza los campos de la factura antes de validar: tipo de persona, documento, dígito de verificación y valores por defecto. */
    protected function prepareInvoice(): void
    {
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
            'needs_invoice' => $this->boolean('needs_invoice'),
            'person_type' => $personType,
            'id_type' => $idType !== '' ? $idType : null,
            'identification' => $number,
            'check_digit' => $personType === 'Company' ? $dv : null,
            'billing_email' => mb_strtolower(trim((string) ($this->input('billing_email') ?: $this->input('email')))),
            'billing_same_as_delivery' => filter_var($this->input('billing_same_as_delivery'), FILTER_VALIDATE_BOOL),
            'vat_responsible' => filter_var($this->input('vat_responsible'), FILTER_VALIDATE_BOOL),
            'fiscal_responsibilities' => $this->input('fiscal_responsibilities') ?: [FiscalCode::NotApplicable->value],
        ]);
    }

    /**
     * @param  bool  $sameAddress  la factura usa la dirección de entrega (no se piden los campos de dirección)
     * @return array<string, mixed>
     */
    protected function invoiceRules(bool $sameAddress = false): array
    {
        $invoice = $this->boolean('needs_invoice');
        $company = $this->input('person_type') === 'Company';
        $ownAddress = $invoice && ! $sameAddress;
        $numeric = in_array($this->input('id_type'), [IdType::CC->value, IdType::NIT->value], true);

        $stateRule = [Rule::in(array_column(Divipola::departments(), 'code'))];
        $cityRule = fn (string $stateField, string $message) => function (string $attribute, mixed $value, \Closure $fail) use ($stateField, $message) {
            if (! Divipola::city((string) $this->input($stateField), (string) $value)) {
                $fail($message);
            }
        };

        return [
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
        ];
    }

    /** El dígito de verificación del NIT tiene que ser el que sale del número (cálculo de la DIAN). */
    protected function validateNitCheckDigit(Validator $validator): void
    {
        if (! $this->boolean('needs_invoice') || $this->input('person_type') !== 'Company' || $validator->errors()->hasAny(['identification', 'check_digit'])) {
            return;
        }

        if (! Nit::isValid((string) $this->input('identification'), $this->input('check_digit'))) {
            $validator->errors()->add('check_digit', 'El dígito de verificación no corresponde a ese NIT. Revísalo (el de '.$this->input('identification').' es '.Nit::checkDigit((string) $this->input('identification')).').');
        }
    }

    /** @return array<string, string> */
    protected function invoiceAttributes(): array
    {
        return [
            'person_type' => 'tipo de persona', 'id_type' => 'tipo de documento', 'identification' => 'número de documento', 'check_digit' => 'dígito de verificación',
            'first_name' => 'nombres', 'last_name' => 'apellidos', 'company_name' => 'razón social', 'fiscal_responsibilities' => 'responsabilidad fiscal',
            'billing_email' => 'correo de facturación', 'billing_address' => 'dirección de facturación', 'billing_state_code' => 'departamento de facturación',
            'billing_city_code' => 'municipio de facturación', 'billing_postal_code' => 'código postal de facturación',
        ];
    }

    /** @return array<string, string> */
    protected function invoiceMessages(): array
    {
        return [
            'identification.regex' => 'El número de documento no es válido: usa solo números (CE y pasaporte pueden llevar letras).',
            'check_digit.required' => 'Escribe el dígito de verificación del NIT.',
            'check_digit.digits' => 'El dígito de verificación es un solo número.',
            'billing_email.email' => 'Escribe un correo de facturación válido.',
        ];
    }
}
