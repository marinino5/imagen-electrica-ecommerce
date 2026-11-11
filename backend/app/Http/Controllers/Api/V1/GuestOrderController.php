<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\UserResource;
use App\Services\ApiAuthService;
use App\Services\CustomerService;
use App\Services\OrderService;
use App\Support\GuestOrderLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Compra
 *
 * Pedidos hechos sin cuenta. Se abren con el enlace firmado que llega por correo (`expires` y `signature`).
 */
class GuestOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CustomerService $customers,
        private readonly ApiAuthService $auth,
    ) {}

    /**
     * Estado de un pedido de invitado
     *
     * Detalle con líneas, datos de facturación, entrega e historial de estados. Sin una firma válida y vigente responde 403
     * (el enlace del correo vale 60 días).
     *
     * @unauthenticated
     *
     * @urlParam code string required Código del pedido. Example: PED-0001
     *
     * @queryParam expires integer required Vencimiento del enlace (viene en el correo). Example: 1799999999
     * @queryParam signature string required Firma del enlace (viene en el correo). Example: 3b7c0f...
     *
     * @apiResource App\Http\Resources\V1\OrderResource
     *
     * @apiResourceModel App\Models\Order with=items,statusHistories
     *
     * @response 403 scenario="Enlace inválido o vencido" {"message":"El enlace no es válido o ya venció. Escríbenos y te lo reenviamos."}
     */
    public function show(Request $request, string $code): OrderResource
    {
        $this->ensureValidLink($request, $code);

        return new OrderResource($this->orders->findGuestOrder($code));
    }

    /**
     * Crear la cuenta con un clic
     *
     * Con los datos de la compra ya llenos: solo falta la contraseña. Devuelve el token como el registro normal y vincula a la cuenta
     * los pedidos de invitado hechos con ese correo. Si el correo o el NIT/cédula ya tienen cuenta responde 422 (la tienda ofrece
     * iniciar sesión). La firma va igual que en el estado del pedido (`expires` y `signature`).
     *
     * @unauthenticated
     *
     * @urlParam code string required Código del pedido. Example: PED-0001
     *
     * @bodyParam expires integer required Vencimiento del enlace. Example: 1799999999
     * @bodyParam signature string required Firma del enlace. Example: 3b7c0f...
     * @bodyParam password string required Mínimo 8 caracteres. Example: ClaveSegura2026
     * @bodyParam marketing_opt_in boolean Acepta recibir promociones. Example: false
     *
     * @response 422 scenario="Ya tiene cuenta" {"message":"Ya existe una cuenta con ese correo. Inicia sesión para ver tus pedidos."}
     */
    public function account(Request $request, string $code): JsonResponse
    {
        $this->ensureValidLink($request, $code);
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:100'],
            'marketing_opt_in' => ['nullable', 'boolean'],
        ], ['password.min' => 'La contraseña debe tener al menos :min caracteres.']);

        $user = $this->customers->registerFromOrder($this->orders->findGuestOrder($code), $data['password'], (bool) ($data['marketing_opt_in'] ?? false));

        return (new UserResource($user))
            ->additional(['token' => $this->auth->issueToken($user), 'token_type' => 'Bearer'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    private function ensureValidLink(Request $request, string $code): void
    {
        abort_unless(
            GuestOrderLink::isValid($code, $request->input('expires'), $request->input('signature')),
            403,
            'El enlace no es válido o ya venció. Escríbenos y te lo reenviamos.',
        );
    }
}
