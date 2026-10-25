<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;

/** Producto como lo ve el administrador: agrega el estado y las fechas. */
class ProductAdminResource extends ProductResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return parent::toArray($request) + [
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
