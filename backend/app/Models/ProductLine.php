<?php

namespace App\Models;

use App\Enums\SaleMode;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Línea de producto: la ficha del catálogo. Agrupa las referencias (variantes por SKU)
 * de una misma familia de equipos, por ejemplo "W22 IEC · carcasa hierro".
 */
#[Fillable([
    'category_id', 'brand_id', 'brand_label', 'name', 'slug', 'description', 'image_key', 'image_url',
    'spec_chips', 'sale_mode', 'is_active', 'is_featured', 'sort_order',
])]
class ProductLine extends Model
{
    protected function casts(): array
    {
        return [
            'spec_chips' => 'array',
            'sale_mode' => SaleMode::class,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** Referencias (SKU) de la línea. */
    public function variants(): HasMany
    {
        return $this->hasMany(Product::class, 'line_id');
    }
}
