<?php

namespace App\Http\Resources\V1;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Customer */
class CustomerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'company_name' => $this->company_name,
            'nit' => $this->nit,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'city' => $this->city,
            'address' => $this->address,
            // Datos de facturación guardados: el formulario de pago los autocompleta
            'doc_type' => $this->doc_type?->value,
            'doc_dv' => $this->doc_dv,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'fiscal_responsibility' => $this->fiscal_responsibility?->value,
            'billing_email' => $this->billing_email,
            'department_code' => $this->department_code,
            'city_code' => $this->city_code,
            'marketing_opt_in' => $this->marketing_opt_in,
            'marketing_opt_in_at' => $this->marketing_opt_in_at,
            'privacy_accepted_at' => $this->privacy_accepted_at,
        ];
    }
}
