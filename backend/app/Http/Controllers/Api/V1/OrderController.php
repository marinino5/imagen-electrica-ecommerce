<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\OrderIndexRequest;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Resources\V1\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @group Pedidos y cotizaciones (cliente)
 *
 * @authenticated
 */
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    /**
     * Mis solicitudes
     *
     * Pedidos y cotizaciones del cliente autenticado, los más recientes primero. Nunca
     * muestra las de otros clientes.
     *
     * @queryParam type string Filtrar por tipo: purchase o quote. Example: quote
     * @queryParam status string Filtrar por estado (pending_payment, paid, shipped, delivered, new, in_review, sent, accepted, rejected, cancelled). Example: new
     * @queryParam per_page integer Solicitudes por página (1 a 50). Example: 10
     *
     * @apiResourceCollection App\Http\Resources\V1\OrderResource
     *
     * @apiResourceModel App\Models\Order with=items paginate=10
     */
    public function index(OrderIndexRequest $request): AnonymousResourceCollection
    {
        return OrderResource::collection(
            $this->orders->paginateForCustomer($request->user()->customer, $request->filters(), $request->perPageValue())
        );
    }

    /**
     * Enviar el carrito
     *
     * El servidor clasifica cada producto: los de venta directa con precio y stock suficiente forman
     * un **pedido** (`purchase`, pendiente de pago); los de cotización, bajo pedido o sin stock
     * suficiente forman una **cotización** (`quote`, nueva y sin precios). Un carrito mixto
     * genera las dos, por eso la respuesta es una lista (pedido primero). Las cantidades
     * de cable pueden ser decimales (metros); las demás unidades, enteras. El envío es gratis
     * al recoger o al superar la compra mínima configurada.
     *
     * @bodyParam items object[] required Líneas del carrito (1 a 100, sin repetir producto).
     * @bodyParam items[].product_id integer required Id del producto. Example: 1
     * @bodyParam items[].quantity number required Cantidad (hasta 2 decimales). Example: 2
     * @bodyParam items[].notes string Nota de la línea. Example: Con certificado de calidad
     * @bodyParam delivery_method string required pickup, local o national. Example: local
     * @bodyParam address string Dirección de entrega. Obligatoria si no es pickup y el cliente no tiene dirección guardada. Example: Zona Industrial Chimitá, bodega 12
     * @bodyParam notes string Observaciones. Example: Entregar en portería
     *
     * @apiResourceCollection App\Http\Resources\V1\OrderResource status=201
     *
     * @apiResourceModel App\Models\Order with=items
     *
     * @response 422 scenario="Carrito vacío" {"message":"El carrito está vacío.","errors":{"items":["El carrito está vacío."]}}
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $orders = $this->orders->createFromCart($request->user()->customer, $request->validated());

        return OrderResource::collection($orders)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Ver una solicitud
     *
     * Detalle con líneas e historial de estados. Si el código no existe o es de otro cliente responde 404.
     *
     * @urlParam code string required Código de la solicitud. Example: PED-0001
     *
     * @apiResource App\Http\Resources\V1\OrderResource
     *
     * @apiResourceModel App\Models\Order with=items,statusHistories
     */
    public function show(Request $request, string $code): OrderResource
    {
        return new OrderResource($this->orders->findForCustomer($request->user()->customer, $code));
    }

    /**
     * Cancelar una solicitud
     *
     * Cambia el estado a `cancelled` (no borra nada y queda en el historial). Solo se puede cancelar un
     * pedido pendiente de pago o una cotización nueva; en cualquier otro estado responde 422.
     *
     * @urlParam code string required Código de la solicitud. Example: COT-0001
     *
     * @apiResource App\Http\Resources\V1\OrderResource
     *
     * @apiResourceModel App\Models\Order with=items,statusHistories
     *
     * @response 422 scenario="Estado que no permite cancelar" {"message":"No se puede pasar pedido PED-0001 de «Enviado» a «Cancelado»."}
     */
    public function cancel(Request $request, string $code): OrderResource
    {
        return new OrderResource($this->orders->cancelForCustomer($request->user()->customer, $code));
    }
}
