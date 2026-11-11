# Cambios desde `arquitectura.pdf` (entrega de la sesión 9)

`docs/arquitectura.pdf` (12 páginas) describe el modelo **v0.1**: 8 entidades, 4 reglas de negocio, C4 niveles 1 a 3 y diccionario de datos. El sistema que se sustenta creció desde entonces. Este documento compara el PDF con el código actual y dice **qué página o diagrama habría que actualizar**. El PDF entregado no se modifica: es la foto de la sesión 9.

Lo que **no cambió** y sigue siendo cierto: la cadena `routes → Controller → Form Request → Service → Repository → Model → MySQL`; que el panel y la API comparten Services y Repositories; que la tienda es un contenedor del sistema y nunca habla con la base de datos; las 8 entidades originales y sus relaciones; los estados de pedido y cotización; los códigos HTTP 200, 201, 204, 401, 403, 404 y 422.

## Resumen por página del PDF

| Páginas | Contenido del PDF | ¿Sigue igual? | Qué cambia |
|---|---|---|---|
| 1 | Portada: 8 entidades, 4 reglas | Parcial | Ahora son 14 tablas de negocio. |
| 2-5 | La idea, la empresa, reglas 1.5 | Sí, con matices | Regla 1 (clasificación): sigue, pero ahora la cotización también puede venir **sin cuenta**. Se añaden reglas nuevas (ver «Reglas»). La nota «cotización como invitado en la etapa empresarial» ya se cumplió; la **compra** sin cuenta sigue en fase 2. |
| 6 | C4 nivel 1 (contexto) | Casi | Sale el visitante sin cuenta; el correo SMTP sigue. Siigo y la pasarela siguen punteados (fase 2). |
| 7 | C4 nivel 2 (contenedores) | Parcial | Falta nombrar el despliegue real y el PDF de ficha técnica. |
| 8 | C4 nivel 3 (componentes) | No | Hay muchos Services, Repositories y controladores nuevos. |
| 9 | Entidad-relación | No | Pasa de 8 a 14 entidades. |
| 10-12 | Diccionario de datos | No | Columnas y tablas nuevas (abajo). |

## Modelo de datos (páginas 9 a 12)

De 8 a **14 tablas de negocio** (más las de Laravel: tokens, caché, colas, sesiones).

| Tabla / columna nueva | Migración | Qué agregar al ER y al diccionario |
|---|---|---|
| **product_lines** (línea de producto: agrupa las referencias de un mismo producto) | `2026_11_06_100000_create_product_lines_table` | `category_id`, `brand_id`, `name`, `slug`, `sale_mode`, `spec_chips`, `image_url`, `is_active`, `is_featured`, `sort_order`. 1:N con `products` y N:1 con `categories` y `brands`. |
| `products.line_id`, `variant_label`, `price_is_simulated`, `cost_price` | misma migración y `2026_10_25_100000_add_cost_price_to_products_table` | Cuatro columnas. El PDF decía «no hay tabla de variantes»: se mantiene (cada referencia sigue siendo un producto), pero ahora se **agrupan en líneas** y cada una lleva su rótulo de variante. |
| `products.price_source` (nulo = precio confirmado; `reference` = de referencia, por confirmar) | `2026_11_12_100000_add_price_source_and_reference_prices` | Una columna. Enum `PriceSource`. |
| **quote_requests** y **quote_request_attachments** (cotización sin cuenta: del catálogo o libre, con adjunto 1:1) | `2026_11_06_110000…`, `2026_11_10_100000…` | Entidades independientes de `orders`: datos de contacto, `items` (json), `type`, `status`, `privacy_accepted_at`, `ip`. |
| **promotions** | `2026_11_07_100000_create_promotions_table` | N:1 con `product_lines`, opcional con `products`. |
| **newsletter_subscriptions** | `2026_11_08_100200…`, `2026_11_14_100000…` | `email` único, `consent`, `consented_at`, `consent_version`, `unsubscribed_at`, `ip`. La baja no borra la prueba del consentimiento. |
| **datasheet_downloads** (cada PDF de ficha con cotización) | `2026_11_13_100000_create_datasheet_downloads_table` | `product_id` (nulo si se elimina), copia de `sku`, `product_name`, `variant_label`, `quantity`, `price_kind`, `unit_price`, `total`, `document_number` (único), `ip`. |
| `customers.type` (empresa o persona natural); `company_name` y `nit` ahora admiten nulo | `2026_10_25_120000_add_type_to_customers_table` | Corregir dos columnas y agregar `type`. |
| `orders.customer_type` | misma migración | Copia del tipo de cliente, como el resto de datos del cliente. |
| `categories.sort_order`, `categories.description` | migraciones de 2026-11 | Las categorías raíz son ahora las **9 familias** de la tienda (Material Eléctrico y Herramientas y Repuestos se fusionaron en «Material Eléctrico y Repuestos»); las hijas son las categorías de cada familia. |

**Decisión que cambia respecto al PDF:** una «familia» no es una tabla nueva, es una categoría raíz (`parent_id` nulo). Las categorías de la primera versión (Sinfín corona, Extractores, Cables THHN…) se fusionaron en las de la tienda con `CatalogLayoutService::migrateLegacyCategories()` (migración `2026_11_14_110000_migrate_legacy_categories`), que además es idempotente.

## Reglas de negocio (páginas 4 y 5)

- **Regla 1, clasificación:** igual. `OrderService` sigue separando pedido y cotización. Novedad: la cotización también puede entrar **sin cuenta** (`QuoteRequestService`, `POST /api/v1/quote-requests` y `/quote-requests/free`) y se atiende desde el panel junto con las demás (`QuoteInboxService`).
- **Regla 2, estados con historial:** igual.
- **Regla 3, totales:** igual (IVA incluido, envío configurable). Se añadió el **IVA de las ventas** como indicador en Análisis (`ReportRepository::salesTotals`).
- **Regla 4, inventario:** igual, pero el **stock ya no se edita a mano** en el panel (es de solo lectura): se sincronizará con Siigo en fase 2. La API de administración conserva su propio comportamiento.
- **Nueva:** precio de referencia. Si `price_source` es `reference`, la tienda lo muestra como «Precio de referencia» y la compra rápida solo aparece si hay stock; si no, se cotiza.
- **Nueva:** ficha técnica en PDF con cotización (`DatasheetService`, `GET /api/v1/datasheet`): numera el documento y registra la descarga.
- **Nueva:** suscripción al boletín con consentimiento versionado (`NewsletterService`): reactivación (volver a suscribirse limpia la baja) y exportación CSV con la prueba.

## C4 (páginas 6 a 8)

- **Nivel 1 (p. 6):** agregar al *visitante sin cuenta* como persona que pide cotización. El resto sigue.
- **Nivel 2 (p. 7):** nombrar los contenedores reales: tienda React en **Cloudflare Pages**; panel Blade + API Laravel en **Render** (Docker); **MySQL en Aiven**. La API genera el PDF de la ficha con `laravel-dompdf` y la tienda le pide el PDF; las imágenes de producto viven en la tienda (el panel antepone la dirección de la tienda a las rutas relativas: `Product::image_src`). El correo SMTP sigue igual.
- **Nivel 3 (p. 8)** — componentes a agregar:
  - Services: `ProductLineService`, `QuoteRequestService`, `QuoteInboxService`, `PromotionService`, `NewsletterService`, `CatalogPriceService`, `CatalogLayoutService`, `DatasheetService`, `InventoryService`, `CustomerAdminService`, `ReportService`, `DashboardService`, `PasswordResetService`.
  - Repositories: `ProductLineRepository`, `PromotionRepository`, `NewsletterRepository`, `DatasheetDownloadRepository`, `QuoteInboxRepository`, `CustomerRepository`, `ReportRepository`, `DashboardRepository`.
  - Controladores de la API: `Api\V1\Admin\ProductController` y `Api\V1\Admin\CategoryController` (los dos CRUD completos), `DatasheetController`, `LineController`, `PromotionController`, `NewsletterController`, `QuoteRequestController`, `OrderController`, `AuthController` y `ProfileController` (las familias salen de `CategoryController`).
  - Panel (Blade): `DashboardController` (Inicio), `OrderController` (pedidos), `QuoteInboxController` (cotizaciones con y sin cuenta), `CustomerController`, `NewsletterController`, `ReportController` (Análisis y Análisis comercial), `ProductController`, `CategoryController`, `BrandController`, `PromotionController`, `DatasheetDownloadController`, `QuoteRequestController` (cotizaciones sin cuenta) y los de acciones sobre un pedido: `OrderStatusController`, `OrderPricingController`, `OrderAssignmentController`.

## API (diccionario de endpoints)

El PDF lista el CRUD de productos y de categorías. Hoy hay **33 rutas** en `/api/v1`:

| Grupo | Rutas |
|---|---|
| Público (sin token) | `GET families`, `categories`, `brands`, `lines`, `lines/{slug}`, `products`, `products/{slug}`, `promotions`, `datasheet`; `POST newsletter`, `quote-requests`, `quote-requests/free` |
| Autenticación | `POST register`, `login`, `password/forgot`, `password/reset`; `POST logout` y `GET/PUT me` con token |
| Pedidos del cliente (token) | `GET orders`, `POST orders`, `GET orders/{code}`, `PATCH orders/{code}/cancel` |
| Administración (token de admin) | CRUD de `admin/products` y de `admin/categories` |

Colección Postman y OpenAPI: `docs/collection.json` y `docs/openapi.yaml` (`composer docs:api`).

## Panel administrativo (no estaba detallado en el PDF)

Rediseñado en esta entrega, con la misma arquitectura (controlador delgado → Service → Repository):

- **Inicio** (`admin.dashboard`): lo que requiere atención (pedidos pendientes y cotizaciones sin responder, lo atrasado primero), cuatro indicadores y una gráfica de 30 días de ventas y cotizaciones.
- **Ventas:** Pedidos, Cotizaciones (con y sin cuenta, `/admin/cotizaciones`) y Fichas descargadas.
- **Catálogo** (solo admin): Productos (stock de solo lectura), Familias y categorías, Marcas, Promociones.
- **Clientes:** listado y ficha con su historial; Suscriptores con baja y exportación CSV.
- **Análisis** (solo admin): Reportes con IVA y Análisis comercial (líneas y familias más cotizadas, fichas más descargadas, cotizaciones y suscriptores).
- Roles sin cambios: `admin` ve todo; `comercial` ve ventas, clientes y suscriptores.

## Fuera de alcance (sigue siendo fase 2)

Compra sin cuenta, pagos PSE, integración con Siigo, precios por cliente, transportadoras, WhatsApp API y verificación de correo. Las investigaciones de esa fase están en `docs/investigacion-fase3.md`.
