<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

/**
 * Registro y perfil del cliente (usuario con rol cliente + ficha de su empresa).
 */
class CustomerService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly CustomerRepository $customers,
    ) {}

    /**
     * Crea el usuario (rol cliente) y su ficha de empresa. Aceptar la política
     * de datos es obligatorio y queda con fecha; el marketing es opcional y
     * también guarda la fecha en que se aceptó.
     *
     * @param  array{name: string, email: string, password: string, company_name: string, nit: string, phone: string, city: string, address?: ?string, marketing_opt_in?: bool}  $data
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->users->createClient([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $marketing = (bool) ($data['marketing_opt_in'] ?? false);

            $this->customers->createFor($user, [
                'company_name' => $data['company_name'],
                'nit' => $data['nit'],
                'contact_name' => $data['name'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'address' => $data['address'] ?? null,
                'marketing_opt_in' => $marketing,
                'marketing_opt_in_at' => $marketing ? now() : null,
                'privacy_accepted_at' => now(),
            ]);

            return $user->load('customer');
        });
    }

    /**
     * Actualiza nombre de contacto y datos de la empresa. El NIT y el correo no
     * se cambian desde aquí. La fecha del marketing se guarda al activarlo y se
     * borra al desactivarlo; si el valor no cambia, no se toca.
     *
     * @param  array{name: string, company_name: string, phone: string, city: string, address?: ?string, marketing_opt_in?: bool}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $customer = $user->customer;

            $this->users->update($user, ['name' => $data['name']]);

            $changes = [
                'contact_name' => $data['name'],
                'company_name' => $data['company_name'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'address' => $data['address'] ?? null,
            ];

            if (array_key_exists('marketing_opt_in', $data) && $data['marketing_opt_in'] !== $customer->marketing_opt_in) {
                $changes['marketing_opt_in'] = $data['marketing_opt_in'];
                $changes['marketing_opt_in_at'] = $data['marketing_opt_in'] ? now() : null;
            }

            $this->customers->update($customer, $changes);

            return $user->refresh()->load('customer');
        });
    }
}
