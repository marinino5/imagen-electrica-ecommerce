<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Inicio y cierre de sesión del panel administrativo (guard "web", sesión).
 * Regla: solo entran usuarios activos con rol admin o comercial.
 */
class PanelAuthService
{
    /**
     * @throws ValidationException si las credenciales fallan o el usuario no puede entrar
     */
    public function login(string $email, string $password, bool $remember = false): User
    {
        $guard = Auth::guard('web');

        // validate() revisa correo y contraseña sin iniciar sesión todavía,
        // para poder revisar el rol y el estado antes de abrirla.
        if (! $guard->validate(['email' => $email, 'password' => $password])) {
            $this->fail('El correo o la contraseña no son correctos.');
        }

        /** @var User $user */
        $user = $guard->getLastAttempted();

        if (! $user->role->canAccessPanel()) {
            $this->fail('Acceso denegado: el panel es solo para el personal de Imagen Eléctrica.');
        }

        if (! $user->is_active) {
            $this->fail('Tu usuario está inactivo. Comunícate con el administrador.');
        }

        $guard->login($user, $remember);

        return $user;
    }

    public function logout(): void
    {
        Auth::guard('web')->logout();
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['email' => $message]);
    }
}
