<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfileRequest;
use App\Http\Requests\Api\V1\UpdateAddressesRequest;
use App\Http\Requests\Api\V1\UpdateBillingRequest;
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
     * Cambia el nombre de contacto y los datos del cliente. El correo, el tipo y el NIT o cédula
     * no se modifican desde aquí. Solo clientes.
     *
     * @bodyParam name string required Nombre de contacto. Example: Laura Gómez
     * @bodyParam company_name string Razón social. Obligatoria para empresas; las personas naturales no la tienen. Example: Constructora Altos S.A.S.
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

    /**
     * Actualizar mis datos de facturación
     *
     * Los mismos campos del paso de facturación del pago, con los nombres de la API de clientes de Siigo (ver `POST /checkout`).
     * `needs_invoice: false` borra los datos guardados. Solo clientes.
     *
     * @bodyParam needs_invoice boolean required Si tiene datos de facturación. Example: true
     * @bodyParam person_type string `Person` o `Company`. Example: Company
     * @bodyParam identification string Número de documento (NIT sin dígito de verificación). Example: 890903938
     * @bodyParam check_digit string Dígito de verificación (empresa). Example: 8
     * @bodyParam company_name string Razón social (empresa). Example: Constructora Altos S.A.S.
     * @bodyParam fiscal_responsibilities string[] Códigos: R-99-PN, O-13, O-15, O-23, O-47. Example: ["O-13"]
     * @bodyParam billing_address string Dirección. Example: Carrera 27 # 45-10
     * @bodyParam billing_state_code string Código DANE del departamento. Example: 68
     * @bodyParam billing_city_code string Código DANE del municipio. Example: 68001
     *
     * @apiResource App\Http\Resources\V1\UserResource
     *
     * @apiResourceModel App\Models\User with=customer
     */
    public function updateBilling(UpdateBillingRequest $request): UserResource
    {
        return new UserResource($this->customers->updateBilling($request->user(), $request->validated()));
    }

    /**
     * Actualizar mis direcciones de entrega
     *
     * Reemplaza la lista completa de direcciones guardadas (hasta 5); una lista vacía las borra. Autocompletan el paso de entrega del pago.
     *
     * @bodyParam addresses object[] required Direcciones.
     * @bodyParam addresses[].state_code string required Código DANE del departamento. Example: 68
     * @bodyParam addresses[].city_code string required Código DANE del municipio. Example: 68276
     * @bodyParam addresses[].neighborhood string required Barrio. Example: Zona Industrial
     * @bodyParam addresses[].road string required Vía. Example: Carrera 9
     * @bodyParam addresses[].number string required Número. Example: # 12-30
     *
     * @apiResource App\Http\Resources\V1\UserResource
     *
     * @apiResourceModel App\Models\User with=customer
     */
    public function updateAddresses(UpdateAddressesRequest $request): UserResource
    {
        return new UserResource($this->customers->updateAddresses($request->user(), $request->validated('addresses')));
    }
}
