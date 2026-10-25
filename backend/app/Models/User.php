<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// role e is_active no son asignables en masa: así un registro desde la API
// no puede enviar role=admin. Se asignan de forma explícita en el código.
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    /** Ficha de empresa (solo usuarios con rol cliente). 1:1 */
    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    /** Pedidos y cotizaciones asignados a este comercial. 1:N */
    public function assignedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'assigned_to');
    }

    /** Cambios de estado que hizo este usuario. 1:N */
    public function statusChanges(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }
}
