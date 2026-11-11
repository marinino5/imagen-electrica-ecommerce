<?php

namespace App\Http\Requests\Api\V1;

use App\Http\Requests\Concerns\InvoiceFields;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/** PUT /me/billing: los datos de facturación guardados (los mismos del paso 3 del pago). Con `needs_invoice` en falso se borran. */
class UpdateBillingRequest extends FormRequest
{
    use InvoiceFields;

    public function authorize(): bool
    {
        return true; // El rol cliente lo controla el middleware de la ruta
    }

    protected function prepareForValidation(): void
    {
        $this->prepareInvoice();
        // El correo de facturación por defecto es el de la cuenta
        $this->merge(['billing_email' => mb_strtolower(trim((string) ($this->input('billing_email') ?: $this->user()?->email)))]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return $this->invoiceRules();
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateNitCheckDigit($validator)];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return $this->invoiceAttributes();
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return $this->invoiceMessages();
    }
}
