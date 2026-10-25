<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Admin, comercial y dos clientes de ejemplo con su empresa.
 * Las contraseñas vienen del .env (SEED_*_PASSWORD).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $seed = config('app.seed');

        $this->createUser('Administrador', $seed['admin_email'], $this->password('admin_password'), UserRole::Admin);
        $this->createUser('Asesor Comercial', $seed['comercial_email'], $this->password('comercial_password'), UserRole::Comercial);

        // Empresas ficticias para pruebas
        $customers = [
            [
                'name' => 'Carlos Andrés Rueda',
                'email' => 'compras@metalmecanicaoriente.test',
                'customer' => [
                    'company_name' => 'Metalmecánica del Oriente S.A.S.',
                    'nit' => '901234567-1',
                    'contact_name' => 'Carlos Andrés Rueda',
                    'phone' => '317 555 0101',
                    'city' => 'Bucaramanga',
                    'address' => 'Zona Industrial Chimitá, bodega 12',
                    'marketing_opt_in' => false,
                    'marketing_opt_in_at' => null,
                ],
            ],
            [
                'name' => 'Laura Patricia Gómez',
                'email' => 'laura.gomez@constructoraaltos.test',
                'customer' => [
                    'company_name' => 'Constructora Altos de Girón S.A.S.',
                    'nit' => '900765432-8',
                    'contact_name' => 'Laura Patricia Gómez',
                    'phone' => '315 555 0202',
                    'city' => 'Girón',
                    'address' => null,
                    'marketing_opt_in' => true,
                    'marketing_opt_in_at' => now(),
                ],
            ],
        ];

        foreach ($customers as $data) {
            $user = $this->createUser($data['name'], $data['email'], $this->password('customer_password'), UserRole::Cliente);
            $user->customer()->create($data['customer'] + ['privacy_accepted_at' => now()]);
        }
    }

    private function createUser(string $name, string $email, string $password, UserRole $role): User
    {
        $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
        // role e is_active no son asignables en masa (ver User)
        $user->forceFill(['role' => $role, 'is_active' => true, 'email_verified_at' => now()])->save();

        return $user;
    }

    private function password(string $key): string
    {
        $password = config("app.seed.$key");

        if (blank($password)) {
            $env = 'SEED_'.strtoupper($key);
            throw new RuntimeException("Falta definir {$env} en el archivo .env");
        }

        return $password;
    }
}
