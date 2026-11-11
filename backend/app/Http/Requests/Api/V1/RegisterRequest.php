<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\CustomerType;
use App\Http\Requests\Concerns\InvoiceFields;
use App\Support\Divipola;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Registro del cliente: usuario + datos de empresa o de persona natural + política de datos.
 * Empresa: razón social y NIT obligatorios. Persona natural: solo el nombre; la cédula (en nit) es opcional.
 */
class RegisterRequest extends FormRequest
{
    use InvoiceFields;

    public function authorize(): bool
    {
        return true;
    }

    /** La tienda actual manda `needs_invoice` (datos de facturación opcionales, como en el pago); la API anterior no lo manda y sigue igual. */
    private function isInvoiceFlow(): bool
    {
        return $this->has('needs_invoice');
    }

    protected function prepareForValidation(): void
    {
        // El correo y el NIT se guardan sin espacios sobrantes; el correo en minúscula.
        $merge = [
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'nit' => trim((string) $this->input('nit')) ?: null,
            'company_name' => trim((string) $this->input('company_name')) ?: null,
        ];

        if ($this->isInvoiceFlow()) {
            $this->prepareInvoice();
            $this->merge($merge + ['billing_email' => mb_strtolower(trim((string) ($this->input('billing_email') ?: $this->input('email'))))]);
            // El tipo de cliente sale del tipo de persona de la factura; sin factura es persona natural
            $company = $this->boolean('needs_invoice') && $this->input('person_type') === 'Company';
            $this->merge([
                'type' => $company ? CustomerType::Company->value : CustomerType::Natural->value,
                'nit' => $this->boolean('needs_invoice') ? $this->input('identification').($company ? '-'.$this->input('check_digit') : '') : null,
                'company_name' => $company ? $this->input('company_name') : null,
                'phone' => preg_replace('/[^\d+]/', '', (string) $this->input('phone')),
            ]);

            return;
        }

        // Si no se indica el tipo se asume empresa (así funcionaban los registros anteriores).
        $this->merge($merge + ['type' => $this->input('type') ?: CustomerType::Company->value]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $new = $this->isInvoiceFlow();
        $invoice = $new && $this->boolean('needs_invoice');
        $nitRules = $invoice ? ['nullable', 'string', 'max:20', 'unique:customers,nit'] : [$new ? 'nullable' : 'required_if:type,'.CustomerType::Company->value, 'nullable', 'string', 'max:20', 'unique:customers,nit'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'type' => ['required', Rule::enum(CustomerType::class)],
            'company_name' => $new ? ['nullable', 'string', 'max:255'] : ['required_if:type,'.CustomerType::Company->value, 'nullable', 'string', 'max:255'],
            'nit' => $nitRules,
            'phone' => ['required', 'string', 'max:30'],
            'city' => [$new ? 'nullable' : 'required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            // Obligatorio: sin aceptar la política de datos no hay cuenta
            'privacy_accepted' => ['required', 'accepted'],
            'marketing_opt_in' => ['nullable', 'boolean'],
            ...($new ? $this->invoiceRules() : []),
        ];
    }

    /** Con factura, el dígito de verificación del NIT tiene que corresponder al número. */
    public function after(): array
    {
        return [fn (Validator $validator) => $this->isInvoiceFlow() && $this->validateNitCheckDigit($validator)];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['type' => 'tipo de cliente'] + ($this->isInvoiceFlow() ? $this->invoiceAttributes() : []);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ($this->isInvoiceFlow() ? $this->invoiceMessages() : []) + [
            'email.unique' => 'Ya existe una cuenta con ese correo.',
            'nit.unique' => 'Ya existe una cuenta con ese NIT o cédula.',
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
