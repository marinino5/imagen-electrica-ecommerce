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
            'marketing_opt_in' => $this->marketing_opt_in,
            'marketing_opt_in_at' => $this->marketing_opt_in_at,
            'privacy_accepted_at' => $this->privacy_accepted_at,
        ];
    }
}
