<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\ApiAuthService;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Autenticación
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly CustomerService $customers,
        private readonly ApiAuthService $auth,
    ) {}

    /**
     * Registrar cliente
     *
     * Crea la cuenta (rol cliente) con los datos de su empresa y devuelve un token listo
     * para usar. Aceptar la política de datos es obligatorio; el marketing es opcional.
     * Máximo 5 intentos por minuto.
     *
     * @unauthenticated
     *
     * @bodyParam name string required Nombre de la persona de contacto. Example: Laura Gómez
     * @bodyParam email string required Correo (único). Example: laura@constructoraaltos.test
     * @bodyParam password string required Mínimo 8 caracteres. Example: ClaveSegura2026
     * @bodyParam company_name string required Razón social. Example: Constructora Altos S.A.S.
     * @bodyParam nit string required NIT (único). Example: 900765432-8
     * @bodyParam phone string required Teléfono. Example: 315 555 0202
     * @bodyParam city string required Ciudad. Example: Bucaramanga
     * @bodyParam address string Dirección de entrega habitual. Example: Carrera 27 # 45-10
     * @bodyParam privacy_accepted boolean required Debe ser true (política de tratamiento de datos). Example: true
     * @bodyParam marketing_opt_in boolean Acepta recibir promociones. Example: false
     *
     * @response 201 scenario="Cuenta creada" {"data":{"id":5,"name":"Laura Gómez","email":"laura@constructoraaltos.test","role":"cliente","customer":{"company_name":"Constructora Altos S.A.S.","nit":"900765432-8","contact_name":"Laura Gómez","phone":"315 555 0202","city":"Bucaramanga","address":null,"marketing_opt_in":false,"marketing_opt_in_at":null,"privacy_accepted_at":"2026-10-25T15:00:00.000000Z"}},"token":"1|abc123...","token_type":"Bearer"}
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->customers->register($request->customerData());

        return $this->tokenResponse($user, $this->auth->issueToken($user), Response::HTTP_CREATED);
    }

    /**
     * Iniciar sesión
     *
     * Devuelve un token Bearer. Sirve para clientes y para el administrador. Máximo 5 intentos
     * por minuto por correo y dirección IP. Credenciales incorrectas responden 401; un usuario
     * inactivo, 403.
     *
     * @unauthenticated
     *
     * @bodyParam email string required Correo. Example: laura@constructoraaltos.test
     * @bodyParam password string required Contraseña. Example: ClaveSegura2026
     *
     * @response 200 scenario="Sesión iniciada" {"data":{"id":5,"name":"Laura Gómez","email":"laura@constructoraaltos.test","role":"cliente","customer":{"company_name":"Constructora Altos S.A.S.","nit":"900765432-8","contact_name":"Laura Gómez","phone":"315 555 0202","city":"Bucaramanga","address":null,"marketing_opt_in":false,"marketing_opt_in_at":null,"privacy_accepted_at":"2026-10-25T15:00:00.000000Z"}},"token":"2|xyz789...","token_type":"Bearer"}
     * @response 401 scenario="Credenciales incorrectas" {"message":"El correo o la contraseña no son correctos."}
     */
    public function login(LoginRequest $request): JsonResponse
    {
        [$user, $token] = $this->auth->login($request->validated('email'), $request->validated('password'));

        return $this->tokenResponse($user, $token);
    }

    /**
     * Cerrar sesión
     *
     * Revoca el token con el que se hizo la petición. Los tokens de otros dispositivos siguen activos.
     *
     * @authenticated
     *
     * @response 204 scenario="Sesión cerrada" {}
     */
    public function logout(Request $request): Response
    {
        $this->auth->logout($request->user());

        return response()->noContent();
    }

    private function tokenResponse($user, string $token, int $status = Response::HTTP_OK): JsonResponse
    {
        return (new UserResource($user))
            ->additional(['token' => $token, 'token_type' => 'Bearer'])
            ->response()
            ->setStatusCode($status);
    }
}
