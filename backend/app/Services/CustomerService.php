<?php

namespace App\Services;

use App\Enums\CustomerType;
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
     * Crea el usuario (rol cliente) y su ficha (empresa o persona natural). Aceptar la política
     * de datos es obligatorio y queda con fecha; el marketing es opcional y
     * también guarda la fecha en que se aceptó.
     *
     * @param  array{type: string, name: string, email: string, password: string, company_name?: ?string, nit?: ?string, phone: string, city: string, address?: ?string, marketing_opt_in?: bool}  $data
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
            $type = CustomerType::from($data['type']);

            $this->customers->createFor($user, [
                'type' => $type,
                // La persona natural no tiene razón social; su cédula va en nit y es opcional
                'company_name' => $type->requiresCompanyData() ? $data['company_name'] : null,
                'nit' => $data['nit'] ?? null,
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
     * Actualiza nombre de contacto y, si es empresa, la razón social. El tipo, el
     * NIT y el correo no se cambian desde aquí. La fecha del marketing se guarda al activarlo y se
     * borra al desactivarlo; si el valor no cambia, no se toca.
     *
     * @param  array{name: string, company_name?: ?string, phone: string, city: string, address?: ?string, marketing_opt_in?: bool}  $data
     */
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $customer = $user->customer;

            $this->users->update($user, ['name' => $data['name']]);

            $changes = [
                'contact_name' => $data['name'],
                'phone' => $data['phone'],
                'city' => $data['city'],
                'address' => $data['address'] ?? null,
            ];

            if ($customer->type->requiresCompanyData()) {
                $changes['company_name'] = $data['company_name'];
            }

            if (array_key_exists('marketing_opt_in', $data) && $data['marketing_opt_in'] !== $customer->marketing_opt_in) {
                $changes['marketing_opt_in'] = $data['marketing_opt_in'];
                $changes['marketing_opt_in_at'] = $data['marketing_opt_in'] ? now() : null;
            }

            $this->customers->update($customer, $changes);

            return $user->refresh()->load('customer');
        });
    }
}
