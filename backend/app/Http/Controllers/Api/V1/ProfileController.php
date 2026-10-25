<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfileRequest;
use App\Http\Resources\V1\UserResource;
use App\Services\CustomerService;
use Illuminate\Http\Request;

/**
 * @group Mi cuenta
 *
 * @authenticated
 */
class ProfileController extends Controller
{
    public function __construct(private readonly CustomerService $customers) {}

    /**
     * Ver mi perfil
     *
     * Usuario autenticado y, si es cliente, los datos de su empresa.
     *
     * @apiResource App\Http\Resources\V1\UserResource
     *
     * @apiResourceModel App\Models\User with=customer
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user()->load('customer'));
    }

    /**
     * Actualizar mi perfil
     *
     * Cambia el nombre de contacto y los datos de la empresa. El correo y el NIT no se
     * modifican desde aquí. Solo clientes.
     *
     * @bodyParam name string required Nombre de contacto. Example: Laura Gómez
     * @bodyParam company_name string required Razón social. Example: Constructora Altos S.A.S.
     * @bodyParam phone string required Teléfono. Example: 315 555 0202
     * @bodyParam city string required Ciudad. Example: Girón
     * @bodyParam address string Dirección de entrega habitual. Example: Carrera 27 # 45-10
     * @bodyParam marketing_opt_in boolean Acepta recibir promociones. Example: true
     *
     * @apiResource App\Http\Resources\V1\UserResource
     *
     * @apiResourceModel App\Models\User with=customer
     */
    public function update(ProfileRequest $request): UserResource
    {
        return new UserResource($this->customers->updateProfile($request->user(), $request->validated()));
    }
}
