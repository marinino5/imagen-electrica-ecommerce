<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DatasheetRequest;
use App\Services\DatasheetService;
use Illuminate\Http\Response;

/**
 * @group Ficha técnica (público)
 */
class DatasheetController extends Controller
{
    public function __construct(private readonly DatasheetService $datasheets) {}

    /**
     * Descargar ficha y cotización (PDF)
     *
     * Genera el PDF con la marca de Imagen Eléctrica: producto, referencia, especificaciones, valor (el mismo precio de la tienda,
     * o «sujeto a cotización») y datos de contacto. Cada descarga queda registrada con su número de documento. No requiere token.
     *
     * @unauthenticated
     *
     * @queryParam sku string required Referencia (SKU) elegida. Example: 12220434
     * @queryParam quantity number Cantidad (1 por defecto; decimales solo en productos por metro). Example: 2
     *
     * @response 200 scenario="PDF" binary
     * @response 404 scenario="Referencia inexistente o inactiva" {"message": "No se encontró lo que buscas."}
     * @response 422 scenario="Cantidad inválida" {"message": "El campo cantidad debe ser al menos 0.01."}
     */
    public function show(DatasheetRequest $request): Response
    {
        $file = $this->datasheets->generate((string) $request->validated('sku'), $request->quantityValue(), $request->ip());

        return response($file['content'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$file['filename'].'"',
            'X-Document-Number' => $file['number'],
            'Access-Control-Expose-Headers' => 'Content-Disposition, X-Document-Number',
        ]);
    }
}
