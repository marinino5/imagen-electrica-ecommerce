<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * Recuperar la contraseña con el Password Broker de Laravel: enlace de un solo uso que vence a los 60 minutos
 * (config/auth.php) y que se guarda cifrado en `password_reset_tokens`.
 */
class PasswordResetService
{
    public function __construct(private readonly UserRepository $users) {}

    /**
     * Envía el enlace si el correo es de un usuario activo. No devuelve nada a propósito: el controlador responde igual
     * exista o no el correo, para no revelar qué cuentas hay. El broker también limita a un envío por minuto por usuario.
     */
    public function sendLink(string $email): void
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! $user->is_active) {
            return;
        }

        Password::sendResetLink(['email' => $user->email]);
    }

    /**
     * Cambia la contraseña con el token recibido por correo, lo invalida y revoca los tokens de sesión de la API
     * (quien tuviera la cuenta abierta en otro dispositivo debe ingresar de nuevo).
     *
     * @throws ValidationException 422 si el enlace no es válido, ya se usó o venció
     */
    public function reset(string $email, string $token, string $password): void
    {
        $status = Password::reset(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $password, 'token' => $token],
            function (User $user, string $newPassword): void {
                if (! $user->is_active) {
                    throw ValidationException::withMessages(['email' => 'Tu usuario está inactivo. Comunícate con Imagen Eléctrica.']);
                }

                $this->users->update($user, ['password' => $newPassword]);
                $user->tokens()->delete();

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['token' => 'El enlace no es válido o ya venció. Pide uno nuevo.']);
        }
    }
}
