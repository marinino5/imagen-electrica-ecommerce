<?php

namespace Database\Seeders\Concerns;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\QueryException;
use RuntimeException;

/**
 * Creación de usuarios para los seeders: idempotente y sin filtrar contraseñas.
 */
trait SeedsUsers
{
    /**
     * Crea el usuario si no existe (por email). Si ya existe lo devuelve tal
     * cual: no se le cambia la contraseña ni el rol, para no pisar lo que se
     * haya modificado después desde el panel.
     */
    protected function seedUser(string $name, string $email, string $password, UserRole $role): User
    {
        $user = User::firstOrNew(['email' => $email]);

        if ($user->exists) {
            return $user;
        }

        // role e is_active no son asignables en masa (ver User)
        $user->fill(['name' => $name, 'password' => $password])
            ->forceFill(['role' => $role, 'is_active' => true, 'email_verified_at' => now()]);

        try {
            $user->save();
        } catch (QueryException $e) {
            // El mensaje de QueryException trae el SQL con todos sus valores,
            // incluido el hash de la contraseña. Se relanza solo con el motivo
            // del motor y sin encadenar la excepción original (su mensaje saldría
            // igual en el trace).
            throw new RuntimeException("No se pudo crear el usuario {$email}: ".($e->errorInfo[2] ?? 'error de base de datos'));
        }

        return $user;
    }

    /** Contraseña de seeder tomada de config('app.seed'), definida en el .env. */
    protected function seedPassword(string $key): string
    {
        $password = config("app.seed.$key");

        if (blank($password)) {
            $env = 'SEED_'.strtoupper($key);
            throw new RuntimeException("Falta definir {$env} en el archivo .env");
        }

        return $password;
    }
}
