<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Tokens de la API (Sanctum). El panel usa sesión (PanelAuthService); la API
 * usa un token Bearer que se crea al registrarse o iniciar sesión.
 */
class ApiAuthService
{
    private const TOKEN_NAME = 'api';

    public function __construct(private readonly UserRepository $users) {}

    /**
     * Valida las credenciales de cualquier usuario activo (el cliente y el
     * administrador usan el mismo login) y le crea un token nuevo.
     *
     * @return array{0: User, 1: string} usuario y token en texto plano
     *
     * @throws AuthenticationException si las credenciales fallan (401)
     * @throws AuthorizationException si el usuario está inactivo (403)
     */
    public function login(string $email, string $password): array
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            throw new AuthenticationException('El correo o la contraseña no son correctos.');
        }

        if (! $user->is_active) {
            throw new AuthorizationException('Tu usuario está inactivo. Comunícate con Imagen Eléctrica.');
        }

        return [$user->load('customer'), $this->issueToken($user)];
    }

    public function issueToken(User $user): string
    {
        return $user->createToken(self::TOKEN_NAME)->plainTextToken;
    }

    /** Revoca solo el token con el que se hizo la petición; los de otros dispositivos siguen. */
    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }
}
