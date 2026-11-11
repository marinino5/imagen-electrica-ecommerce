<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCheckoutRequest;
use App\Http\Resources\V1\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @group Compra
 */
class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkout) {}

    /**
     * Comprar (con o sin cuenta)
     *
     * Envía el carrito con los datos de facturación electrónica (DIAN, compatibles con Siigo) y la dirección de entrega. **No necesita cuenta**:
     * sin token es un invitado y recibe un correo con el resumen y un enlace seguro para ver el estado; con token (cliente) los datos
     * quedan guardados en su perfil. El servidor clasifica cada producto (pedido, cotización o ambos), igual que `POST /orders`.
     * Límite de 10 compras por minuto y 40 por hora por IP. `website` es un campo trampa contra bots: debe enviarse vacío.
     *
     * - Persona natural: `doc_type` (CC, CE, PP o NIT), `doc_number`, `first_name`, `last_name`.
     * - Empresa: `doc_number` = NIT sin dígito de verificación, `nit_dv` (se valida contra el NIT), `company_name`, `fiscal_responsibility`, `contact_name`.
     * - Entrega a domicilio: departamento y municipio (códigos DANE de `GET /locations/...`), barrio, vía y número; o `delivery_same_as_billing`.
     *
     * @unauthenticated
     *
     * @bodyParam items object[] required Líneas del carrito (1 a 100, sin repetir producto).
     * @bodyParam items[].product_id integer required Id del producto. Example: 1
     * @bodyParam items[].quantity number required Cantidad (hasta 2 decimales). Example: 2
     * @bodyParam person_type string required `natural` o `company`. Example: company
     * @bodyParam doc_type string Tipo de documento (obligatorio en persona natural): CC, CE, PP o NIT. Example: CC
     * @bodyParam doc_number string required Número de documento (NIT sin dígito de verificación). Example: 900123456
     * @bodyParam nit_dv string Dígito de verificación del NIT (obligatorio en empresa). Example: 8
     * @bodyParam first_name string Nombres (persona natural). Example: Laura
     * @bodyParam last_name string Apellidos (persona natural). Example: Gómez
     * @bodyParam company_name string Razón social (empresa). Example: Constructora Altos S.A.S.
     * @bodyParam fiscal_responsibility string Responsabilidad fiscal (empresa): vat_responsible, not_vat_responsible, large_taxpayer, self_withholder o simple_regime. Example: vat_responsible
     * @bodyParam contact_name string Persona de contacto (empresa). Example: Laura Gómez
     * @bodyParam billing_email string required Correo de facturación electrónica. Example: facturacion@constructoraaltos.test
     * @bodyParam phone string required Celular colombiano de 10 dígitos. Example: 3155550202
     * @bodyParam billing_address string required Dirección de facturación. Example: Carrera 27 # 45-10
     * @bodyParam department_code string required Código DANE del departamento. Example: 68
     * @bodyParam city_code string required Código DANE del municipio. Example: 68001
     * @bodyParam delivery_method string required pickup, local o national. Example: local
     * @bodyParam delivery_same_as_billing boolean La entrega es en la dirección de facturación. Example: false
     * @bodyParam delivery_department_code string Departamento de entrega (si no es pickup ni igual a facturación). Example: 68
     * @bodyParam delivery_city_code string Municipio de entrega. Example: 68276
     * @bodyParam delivery_neighborhood string Barrio. Example: Zona Industrial
     * @bodyParam delivery_road string Vía (tipo y nombre). Example: Carrera 9
     * @bodyParam delivery_number string Número. Example: # 12-30
     * @bodyParam delivery_complement string Complemento. Example: Bodega 3
     * @bodyParam delivery_postal_code string Código postal (6 dígitos, opcional). Example: 681004
     * @bodyParam delivery_notes string Indicaciones de entrega. Example: Entregar en portería
     * @bodyParam notes string Observaciones. Example: Con certificado de calidad
     * @bodyParam privacy_accepted boolean required Autoriza el tratamiento de datos (Ley 1581 de 2012). Example: true
     * @bodyParam save_to_profile boolean Guardar los datos en el perfil (solo con cuenta). Example: true
     *
     * @apiResourceCollection App\Http\Resources\V1\OrderResource status=201
     *
     * @apiResourceModel App\Models\Order with=items
     *
     * @response 422 scenario="NIT con dígito de verificación incorrecto" {"message":"El dígito de verificación no corresponde a ese NIT. Revísalo (el de 900123456 es 8).","errors":{"nit_dv":["El dígito de verificación no corresponde a ese NIT. Revísalo (el de 900123456 es 8)."]}}
     */
    public function store(StoreCheckoutRequest $request): JsonResponse
    {
        // Un bot que llena el campo trampa recibe la misma respuesta, pero no se guarda nada
        if ($request->isBot()) {
            return response()->json(['data' => [], 'meta' => ['guest' => true, 'account_exists' => false]], Response::HTTP_CREATED);
        }

        // Token opcional: solo un cliente con cuenta compra «como cliente»; cualquier otra persona (o rol) es invitado
        $user = $request->user('sanctum');
        $user = $user?->role === UserRole::Cliente ? $user : null;

        $result = $this->checkout->place($user, $request->validated());

        return OrderResource::collection($result['orders'])
            ->additional(['meta' => ['guest' => $result['guest'], 'account_exists' => $result['account_exists']]])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
