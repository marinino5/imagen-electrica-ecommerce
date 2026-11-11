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
1. Entrar como **comercial**: ve pedidos, cotizaciones, dashboard; **no** ve Categorías/Marcas/Productos (403 al forzar la URL, p. ej. `/admin/categories`).
2. Salir y entrar como **admin**: dashboard con el gráfico de solicitudes por estado.
3. **CRUD con búsqueda y paginación**: Categorías → buscar, crear «Demostración», editar, intentar borrar una con productos (mensaje de regla de negocio) y borrar la vacía.
4. **Regla de negocio en vivo**: abrir una cotización `new` → «Iniciar revisión» → intentar «Enviar al cliente» sin precios → error «faltan precios»; poner precios y enviar; ver el historial de estados.
5. Mostrar la página 404 propia (`/admin/no-existe`).

### 3. API en Postman (3 min)
1. `Autenticación → Iniciar sesión (administrador)`: guarda el token solo en `{{token}}`.
2. `Categorías (administrador) → Listar` (200) → `Crear` (201) → `Ver` (200) → `Actualizar` (200) → `Eliminar` (204).
3. Errores con ejemplos guardados: sin token → **401**; id inexistente → **404**; `Crear` sin `name` → **422**; borrar una categoría con productos → **422**.
4. Mostrar un 403: token de cliente en `/api/v1/admin/products`.
5. Límite de peticiones: 6 logins seguidos fallidos → **429** (ver tabla abajo).

### 4. Cliente externo en vivo (4 min)
1. Tienda → iniciar sesión (o crear cuenta) con el cliente de ejemplo.
2. Agregar al carrito un producto de venta directa con stock y uno «solo cotización» (carrito mixto).
3. Enviar la solicitud: la tienda muestra **dos** códigos (PED-xxxx y COT-xxxx).
4. Refrescar el panel: aparecen ambas, con los datos del cliente copiados, y el gráfico cambia.
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
| **Límite de peticiones** | `backend/app/Providers/AppServiceProvider.php` · `RateLimiter::for(...)` (l. 43-68) y `backend/routes/api.php` (middleware `throttle:*`); panel: `routes/web.php` l. 31 | General API: 60/min por IP (`api`). Login API: 5/min por correo+IP (`api-login`). Registro: 5/min. Cotizaciones públicas: 5/min y 20/h. Login del panel: `throttle:5,1`. El 429 se traduce en `bootstrap/app.php` (`HttpExceptionInterface`). *Cambio típico*: `Limit::perMinute(5)` → otro número. |
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
