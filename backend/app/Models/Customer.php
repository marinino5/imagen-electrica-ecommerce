<?php

namespace App\Models;

use App\Enums\CustomerType;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'type', 'company_name', 'nit', 'contact_name', 'phone', 'city', 'address',
    'marketing_opt_in', 'marketing_opt_in_at', 'privacy_accepted_at',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /** Igual que el valor por defecto de la tabla, para que el modelo recién creado ya tenga tipo. */
    protected $attributes = ['type' => 'company'];

    protected function casts(): array
    {
        return [
            'type' => CustomerType::class,
            'marketing_opt_in' => 'boolean',
            'marketing_opt_in_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    /** Usuario con el que inicia sesión. 1:1 (inversa) */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Pedidos y cotizaciones de la empresa. 1:N */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
