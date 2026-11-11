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
            // Datos de facturación guardados (nombres de la API de Siigo): el formulario de pago los autocompleta
            'id_type' => $this->id_type?->value,
            'check_digit' => $this->check_digit,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'commercial_name' => $this->commercial_name,
            'vat_responsible' => $this->vat_responsible,
            'fiscal_responsibilities' => $this->fiscal_responsibilities,
            'billing_email' => $this->billing_email,
            'state_code' => $this->state_code,
            'city_code' => $this->city_code,
            'postal_code' => $this->postal_code,
            'delivery_addresses' => $this->delivery_addresses ?? [],
            'marketing_opt_in' => $this->marketing_opt_in,
            'marketing_opt_in_at' => $this->marketing_opt_in_at,
            'privacy_accepted_at' => $this->privacy_accepted_at,
        ];
    }
}
