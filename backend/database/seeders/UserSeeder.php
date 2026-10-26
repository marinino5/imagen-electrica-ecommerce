<?php

namespace Database\Seeders;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use Database\Seeders\Concerns\SeedsUsers;
use Illuminate\Database\Seeder;

/**
 * Admin, comercial y tres clientes de ejemplo: dos empresas y una persona natural.
 * Las contraseñas vienen del .env (SEED_*_PASSWORD). Se puede ejecutar varias
 * veces: lo que ya existe (por email) no se toca.
 */
class UserSeeder extends Seeder
{
    use SeedsUsers;

    public function run(): void
    {
        $seed = config('app.seed');

        $this->seedUser('Administrador', $seed['admin_email'], $this->seedPassword('admin_password'), UserRole::Admin);
        $this->seedUser('Asesor Comercial', $seed['comercial_email'], $this->seedPassword('comercial_password'), UserRole::Comercial);

        // Empresas ficticias para pruebas
        $customers = [
            [
                'name' => 'Carlos Andrés Rueda',
                'email' => 'compras@metalmecanicaoriente.test',
                'customer' => [
                    'type' => CustomerType::Company,
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
                    'type' => CustomerType::Company,
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
            [
                'name' => 'Sergio Alejandro Pardo',
                'email' => 'sergio.pardo@correo.test',
                'customer' => [
                    'type' => CustomerType::Natural,
                    'company_name' => null,
                    'nit' => null, // la cédula es opcional
                    'contact_name' => 'Sergio Alejandro Pardo',
                    'phone' => '312 555 0303',
                    'city' => 'Floridablanca',
                    'address' => null,
                    'marketing_opt_in' => false,
                    'marketing_opt_in_at' => null,
                ],
            ],
        ];

        foreach ($customers as $data) {
            $user = $this->seedUser($data['name'], $data['email'], $this->seedPassword('customer_password'), UserRole::Cliente);
            $user->customer()->firstOrCreate([], $data['customer'] + ['privacy_accepted_at' => now()]);
        }
    }
}
