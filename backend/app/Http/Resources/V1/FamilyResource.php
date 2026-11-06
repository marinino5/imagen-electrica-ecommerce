<?php

namespace App\Http\Resources\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Familia del portafolio (categoría sin padre) con sus categorías y el número de líneas de cada una.
 *
 * @mixin Category
 */
class FamilyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'lines_count' => (int) $this->children->sum('product_lines_count'),
            'categories' => $this->children->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'lines_count' => (int) $c->product_lines_count,
            ])->values(),
        ];
    }
}
