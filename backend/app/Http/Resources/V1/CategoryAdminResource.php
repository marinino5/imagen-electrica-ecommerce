<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;

/** Categoría como la ve el administrador: agrega el estado, el orden y las fechas. */
class CategoryAdminResource extends CategoryResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return parent::toArray($request) + [
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
