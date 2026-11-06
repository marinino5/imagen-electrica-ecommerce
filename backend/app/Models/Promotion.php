<?php

namespace App\Models;

use Database\Factories\PromotionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Promoción del momento: descuento (%) o precio especial sobre una línea o una de sus referencias.
 * Está vigente cuando está activa y hoy cae entre starts_at y ends_at (ambos días incluidos).
 */
#[Fillable(['title', 'description', 'product_line_id', 'product_id', 'discount_percent', 'special_price', 'starts_at', 'ends_at', 'is_active'])]
class Promotion extends Model
{
    /** @use HasFactory<PromotionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
            'special_price' => 'decimal:2',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(ProductLine::class, 'product_line_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Activas y dentro de su vigencia (fecha de hoy en la zona horaria de la app). */
    public function scopeCurrent(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->where('is_active', true)->whereDate('starts_at', '<=', $today)->whereDate('ends_at', '>=', $today);
    }

    public function isCurrent(): bool
    {
        return $this->is_active && $this->starts_at->lte(today()) && $this->ends_at->gte(today());
    }
}
