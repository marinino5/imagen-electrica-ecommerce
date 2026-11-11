<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Notifications\ResetPasswordNotification;
use App\Support\TextCase;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
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

    /** Nombre con mayúscula inicial (si llega todo en minúscula o todo en mayúscula). */
    protected function name(): Attribute
    {
        return Attribute::set(fn (?string $v) => TextCase::person($v));
    }

    /** El correo de recuperación lleva la marca de la tienda y el enlace va a la tienda, no al panel. */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
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

    /** Iniciales para el avatar: "Asesor Comercial" => "AC". */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word) => Str::upper(Str::substr($word, 0, 1)))
            ->implode('');
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }
}
