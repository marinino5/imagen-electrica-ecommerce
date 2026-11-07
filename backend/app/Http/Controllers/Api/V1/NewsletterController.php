<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreNewsletterRequest;
use App\Services\NewsletterService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Boletín
 */
class NewsletterController extends Controller
{
    public function __construct(private readonly NewsletterService $newsletter) {}

    /**
     * Suscribirse al boletín
     *
     * Público: no necesita token. Límite de 5 intentos por minuto y 20 por hora desde la misma IP. Se guarda el correo,
     * la fecha y la IP de la autorización de tratamiento de datos (Ley 1581 de 2012). Un correo repetido responde igual.
     * `website` es un campo trampa contra bots: debe enviarse vacío o no enviarse.
     *
     * @bodyParam email string required Correo. Example: laura@empresa.co
     * @bodyParam privacy_accepted boolean required Autoriza el tratamiento de datos. Example: true
     *
     * @response 201 {"data":{"message":"Listo, te suscribimos al boletín."}}
     */
    public function store(StoreNewsletterRequest $request): JsonResponse
    {
        // Un bot que llena el campo trampa recibe la misma respuesta, pero no se guarda nada
        if (! $request->isBot()) {
            $this->newsletter->subscribe($request->validated('email'), $request->ip());
        }

        return response()->json(
            ['data' => ['message' => 'Listo, te suscribimos al boletín.']],
            Response::HTTP_CREATED,
        );
    }
}
