<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreQuoteRequestRequest;
use App\Services\QuoteRequestService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Cotización sin cuenta
 */
class QuoteRequestController extends Controller
{
    public function __construct(private readonly QuoteRequestService $quotes) {}

    /**
     * Enviar una cotización
     *
     * Público: no necesita token. Límite de 5 solicitudes por minuto y 20 por hora desde la misma IP.
     * `website` es un campo trampa contra bots: debe enviarse vacío o no enviarse.
     *
     * @bodyParam name string required Nombre. Example: Laura Gómez
     * @bodyParam phone string required Teléfono o WhatsApp. Example: 3001234567
     * @bodyParam email string required Correo. Example: laura@empresa.co
     * @bodyParam city string required Ciudad. Example: Bucaramanga
     * @bodyParam company string Empresa. Example: Constructora Demo SAS
     * @bodyParam nit string NIT. Example: 900123456
     * @bodyParam message string Mensaje. Example: Necesito 3 motores para una bomba.
     * @bodyParam privacy_accepted boolean required Acepta la política de datos. Example: true
     * @bodyParam items object[] required Renglones (1 a 100).
     * @bodyParam items[].product_id integer Id de la referencia. Example: 12
     * @bodyParam items[].line string Slug de la línea (para líneas bajo pedido sin referencias). Example: motorreductor-sinfin-wcg01
     * @bodyParam items[].quantity number required Cantidad. Example: 3
     *
     * @response 201 {"data":{"message":"Recibimos tu solicitud. Un asesor te responderá pronto."}}
     */
    public function store(StoreQuoteRequestRequest $request): JsonResponse
    {
        // Un bot que llena el campo trampa recibe la misma respuesta, pero no se guarda nada
        if (! $request->isBot()) {
            $this->quotes->create($request->validated(), $request->ip());
        }

        return response()->json(
            ['data' => ['message' => 'Recibimos tu solicitud. Un asesor te responderá pronto.']],
            Response::HTTP_CREATED,
        );
    }
}
