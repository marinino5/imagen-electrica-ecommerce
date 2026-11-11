# Guion de sustentación (20 minutos)

URLs públicas
- Panel y API: https://imagen-electrica-ecommerce.onrender.com (panel en `/admin`, API en `/api/v1`)
- Tienda (cliente externo): https://imagen-electrica.pages.dev
- Documentación de la API: https://imagen-electrica-ecommerce.onrender.com/docs

> Render gratis se duerme: abre el panel 1-2 minutos antes de empezar para despertarlo.

## Antes de entrar (preparación, 5 min)

1. Abrir en pestañas: panel (`/admin`), tienda, Postman con `docs/collection.json` importada, GitHub (ramas y Pull Requests) y `docs/arquitectura.pdf`.
2. Postman: variables de colección `baseUrl` (ya viene con Render) y `adminPassword` (la del administrador).
3. Tener a mano dos sesiones: administrador (`admin@imagenelectrica.test`) y un cliente de ejemplo.
4. Una categoría de prueba para crear/borrar en vivo (ej. «Demostración») y un producto con stock bajo.

## Minuto a minuto

### 1. Problema y C4 (3 min)
- **Usuario real**: Imagen Eléctrica, distribuidora de material eléctrico industrial en Bucaramanga. Vende por WhatsApp y teléfono; cotizar y confirmar stock es manual y lento.
- **Problema** (5 líneas): el cliente industrial no ve el catálogo con referencias ni sabe si hay stock; las cotizaciones se pierden en chats; el equipo comercial no tiene un solo lugar con pedidos y estados.
- **Solución**: tienda B2B donde el cliente compra lo disponible o pide cotización de lo demás, y un panel donde el equipo gestiona todo.
- **C4**: mostrar contexto (cliente, comercial/admin, tienda y panel), contenedores (React en Cloudflare Pages · Laravel con panel Blade + API en Render · MySQL en Aiven) y componentes (Controller → Form Request → Service → Repository → Model). El cliente React es un **contenedor del sistema**, no un sistema externo.
- **Dos reglas propias** (mínimo exigido): clasificación compra/cotización y estados con historial (más stock y límite de peticiones).

### 2. Frontend en la URL pública (5 min)
1. Entrar como **comercial**: ve Inicio, Ventas (Pedidos, Cotizaciones, Fichas) y Clientes; **no** ve Catálogo ni Análisis (403 al forzar la URL, p. ej. `/admin/categories`).
2. Salir y entrar como **admin**: Inicio con «Requiere tu atención», cuatro indicadores y la gráfica de ventas y cotizaciones de 30 días. Recorrer las secciones: Ventas · Catálogo · Clientes (con Suscriptores) · Análisis.
3. **CRUD con búsqueda y paginación**: Catálogo → Familias y categorías → buscar, crear «Demostración», editar, intentar borrar una con productos (mensaje de regla de negocio) y borrar la vacía.
4. **Regla de negocio en vivo**: Ventas → Cotizaciones (están juntas las de clientes con cuenta y las enviadas sin cuenta, con su origen); abrir una cotización `new` → «Iniciar revisión» → intentar «Enviar al cliente» sin precios → error «faltan precios»; poner precios y enviar; ver el historial de estados.
5. Mostrar la página 404 propia (`/admin/no-existe`).

### 3. API en Postman (3 min)
1. `Autenticación → Iniciar sesión (administrador)`: guarda el token solo en `{{token}}`.
2. `Categorías (administrador) → Listar` (200) → `Crear` (201) → `Ver` (200) → `Actualizar` (200) → `Eliminar` (204).
3. Errores con ejemplos guardados: sin token → **401**; id inexistente → **404**; `Crear` sin `name` → **422**; borrar una categoría con productos → **422**.
4. Mostrar un 403: token de cliente en `/api/v1/admin/products`.
5. Límite de peticiones: 6 logins seguidos fallidos → **429** (ver tabla abajo).

### 4. Cliente externo en vivo (4 min)
1. Tienda **sin iniciar sesión** (compra de invitado): agregar al carrito un producto con precio (el botón «Agregar» de la tarjeta).
2. En el carrito, **un solo bloque** reúne los productos, la entrega y el total (sin desglose de base e IVA: «IVA incluido»). La entrega se elige ahí mismo (recoger o domicilio; departamento → municipio con código DANE) y al elegir el municipio aparece el envío aproximado (etiqueta «local» o «nacional», con la tabla por zona de `config/shop.php`; el servidor fija el valor real). Debajo hay tres pasos: **Tus datos** (nombre, celular, correo y dirección si es domicilio), **Facturación** (interruptor «Necesito factura…»: persona natural o empresa con los campos de la API de clientes de Siigo; el NIT calcula y valida su dígito de verificación) y **Confirmar** (autorización de datos con versión y fecha).
3. Confirmar: la tienda muestra el código PED-xxxx, el correo con el resumen y el enlace seguro (`/pedido/PED-xxxx?…`) y «Crea tu cuenta con un clic» (solo falta la contraseña). Probar el enlace: vuelve a mostrar el estado; uno alterado responde 403.
4. Refrescar el panel: el pedido aparece en Pedidos con la etiqueta **Invitado** y, al abrirlo, los datos de facturación (con los nombres de campo de Siigo, o «no pidió factura») y la dirección de entrega en columnas separadas (ver `docs/siigo-mapeo.md`).
5. Opcional: con cuenta, el mismo formulario llega autocompletado desde el perfil; un carrito mixto envía lo que no tiene precio como cotización (bloque «Pide tu cotización»).
5. Opcional: en el panel, confirmar el pago del pedido y mostrar que el stock bajó.

### 5. Preguntas y cambio de código (5 min)
Usa la tabla siguiente; cada fila dice dónde está la regla y qué cambiar.

## Reglas de negocio: archivo y método exactos

| Regla | Archivo · método | Qué decir / cómo modificarla |
|---|---|---|
| **Compra vs. cotización** (regla 1) | `backend/app/Services/OrderService.php` · `createFromCart()` (l. 224) usa `isDirectPurchase()` (l. 410) | Un producto va a pedido solo si `sale_mode = direct`, tiene precio **y** `InventoryService::canFulfill()` es verdadero. El resto forma una cotización. Un carrito mixto crea las dos. *Cambio típico*: exigir una cantidad mínima en `isDirectPurchase()`. |
| **Validación de stock** (regla 4) | `backend/app/Services/InventoryService.php` · `canFulfill()` (l. 28) y `deductForOrder()` (l. 41) | `canFulfill` decide pedido vs. cotización al enviar el carrito. Al **confirmar el pago**, `deductForOrder()` bloquea las filas (`lockMany`), suma cantidades repetidas, redondea hacia arriba los metros (`unitsFor`, l. 22) y lanza `BusinessRuleException` → **422** «Stock insuficiente…». Corre dentro de la transacción de `changeStatus()`. |
| **Estados del pedido/cotización** (regla 2) | `backend/app/Services/OrderService.php` · constante `TRANSITIONS` (l. 51) y `changeStatus()` (l. 153) | Todo lo que no esté en `TRANSITIONS` responde 422. Cada cambio guarda una fila en `order_status_histories` (`addHistory`). Pedido: pending_payment → paid → shipped → delivered (cancelar solo desde pending_payment). Cotización: new → in_review → sent → accepted/rejected (cancelar solo desde new). *Cambio típico*: permitir `paid → cancelled` agregando `OrderStatus::Cancelled` a `'paid'`. |
| **Cotización sin precios no se envía** (regla 3) | `OrderService.php` · `changeStatus()` (l. 168-176) y `missingPrices()` (l. 138) | Al pasar a `sent` cuenta las líneas con `unit_price` nulo; si hay alguna, 422. |
| **Cotización aceptada genera pedido** | `OrderService.php` · `createPurchaseFromQuote()` (l. 337), llamado desde `changeStatus()` (l. 186) | El pedido nuevo lleva `quote_id`; la cotización se conserva. |
| **Envío gratis sobre la compra mínima** | `OrderService.php` · `shippingCost()` (l. 249); valor en `backend/config/shop.php` (`free_shipping_from`, env `SHOP_FREE_SHIPPING_FROM`) | Recoger en tienda o superar el mínimo → 0. |
| **Límite de peticiones** | `backend/app/Providers/AppServiceProvider.php` · `RateLimiter::for(...)` (l. 43-68) y `backend/routes/api.php` (middleware `throttle:*`); panel: `routes/web.php` l. 34 | General API: 60/min por IP (`api`). Login API: 5/min por correo+IP (`api-login`). Registro: 5/min. Cotizaciones públicas: 5/min y 20/h. Login del panel: `throttle:5,1`. El 429 se traduce en `bootstrap/app.php` (`HttpExceptionInterface`). *Cambio típico*: `Limit::perMinute(5)` → otro número. |
| **Ficha y cotización en PDF** | `backend/app/Services/DatasheetService.php` · `generate()`; ruta `GET /api/v1/datasheet` (`routes/api.php`), límite `api-datasheet` en `AppServiceProvider.php` | Calcula el total con el MISMO precio de la tienda (`prices()`: el total es precio × cantidad y se desglosa la base y el IVA con el `tax_rate`), decide si es precio de venta, de referencia o solo cotización (`priceKind()`), registra la descarga (`datasheet_downloads`, número IE-FT-2026-00123) y renderiza `resources/views/pdf/datasheet.blade.php`. En el panel: *Fichas descargadas*. |
| **Compra sin cuenta (invitado)** | `backend/app/Services/CheckoutService.php` · `place()` e `identity()`; validación en `Requests/Api/V1/StoreCheckoutRequest.php`; ruta `POST /api/v1/checkout` | Arma las columnas de facturación (DIAN) y de entrega del pedido y delega la clasificación en `OrderService::createFromCart()` (la misma regla pedido/cotización). El NIT se valida con `Support/Nit.php` (dígito de verificación, módulo 11); el municipio debe pertenecer al departamento (`Support/Divipola.php`, 1.122 municipios DANE). Anti-bot: campo trampa `website` y límite `api-checkout` (10/min y 40/h por IP). *Cambio típico*: exigir la dirección de facturación solo a empresas. |
| **Enlace seguro del pedido de invitado** | `backend/app/Support/GuestOrderLink.php` y `Api/V1/GuestOrderController.php` | URL firmada con vencimiento (60 días, `config('shop.guest_link_days')`): sin firma válida, 403. Desde ahí se crea la cuenta con un clic (`CustomerService::registerFromOrder`). |
| **Stock aún sin usar (Siigo)** | `config('shop.track_stock')` (`SHOP_TRACK_STOCK`), `InventoryService::canFulfill()` | Apagado: todo producto con precio se compra y el pago no descuenta unidades; la tienda nunca dice «agotado». Al conectar Siigo se enciende y vuelven las reglas 1 y 4. |
| **Cotizaciones con y sin cuenta juntas** | `backend/app/Services/QuoteInboxService.php` · `paginate()`, `pending()`, `countPending()`; consulta `QuoteInboxRepository.php` (UNION de `orders` tipo cotización y `quote_requests`) | Une las dos fuentes en una sola bandeja (`/admin/cotizaciones`), con código `SOL-NNNN` para las de sin cuenta y la marca de atrasada pasadas `OrderService::QUOTE_RESPONSE_HOURS` horas. El Inicio usa el mismo servicio. |
| **Inicio del panel** | `backend/app/Services/DashboardService.php` · `overview()` | Cuatro indicadores (ventas del mes con comparación, pedidos nuevos, cotizaciones pendientes, tiempo medio de respuesta), la lista de atención (atrasadas primero) y la serie de 30 días. Las consultas están en `DashboardRepository`. |
| **Baja del boletín** | `backend/app/Services/NewsletterService.php` · `unsubscribe()` y `csv()` | La baja guarda `unsubscribed_at` y **no borra** el consentimiento (prueba legal); volver a suscribirse la reactiva. El CSV incluye versión del texto y fecha. |
| **Cancelar es cambio de estado** | `OrderService.php` · `cancelForCustomer()` (l. 201); ruta `PATCH /api/v1/orders/{code}/cancel` | No borra: usa `changeStatus()` y por eso hereda las transiciones. |
| **Borrar categoría con productos** | `backend/app/Services/CategoryService.php` · `delete()` | Lanza `BusinessRuleException` → 422 en la API / aviso rojo en el panel. |
| **Roles** | `routes/api.php` (`role:admin`, `role:cliente`) y `routes/web.php` (`role:admin,comercial`) | Comercial entra al panel, pero solo admin gestiona catálogo. |

## Dónde está cada pieza de la arquitectura

`routes/api.php` → `app/Http/Controllers/Api/V1/Admin/CategoryController.php` → `Requests/Api/V1/CategoryRequest.php` (valida) → `app/Services/CategoryService.php` (reglas) → `app/Repositories/CategoryRepository.php` (Eloquent) → `app/Models/Category.php` → MySQL. La respuesta sale por `Resources/V1/CategoryAdminResource.php`. El panel usa el **mismo** `CategoryService` (`Controllers/Admin/CategoryController.php`).

## Comandos útiles

```bash
cd backend
php artisan test                      # todas las pruebas
php artisan test --filter=AdminCategoryApiTest
php artisan route:list --path=api/v1/admin
php artisan migrate:fresh --seed      # base local limpia con datos
bash ../docs/verificar-api.sh         # comprueba la API de Render
```

## Preguntas probables

- *¿Por qué Service y Repository?* El panel y la API comparten reglas; el controlador solo traduce HTTP.
- *¿Por qué copiar datos del cliente y producto en el pedido?* Para que el historial no cambie si luego se edita o borra el producto.
- *¿Por qué Sanctum y no sesión en la API?* El cliente React vive en otro dominio y usa token Bearer; el panel sí usa sesión.
- *¿Cómo se evita vender más de lo que hay?* Dos filtros: `canFulfill` al armar el carrito y `deductForOrder` (con bloqueo de filas) al confirmar el pago.
- *¿Qué pasa si el servidor falla?* Con `APP_DEBUG=false` se ve la página 500 propia y la API responde un JSON sin detalles (`tests/Feature/ErrorPagesTest.php`).
