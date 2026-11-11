# Cambios del modelo y la arquitectura desde `arquitectura.pdf`

El documento de arquitectura se entregó en la sesión 9 (modelo v0.1, 24 oct 2026). Desde entonces el sistema creció. Para que el PDF coincida con lo que se sustenta, hay que actualizar:

## Modelo de datos (diagrama entidad–relación y diccionario)

| Cambio | Dónde (migración) | Qué agregar al PDF |
|---|---|---|
| Entidad nueva **product_lines** (líneas del catálogo; agrupa referencias del mismo producto) | `2026_11_06_100000_create_product_lines_table` | Entidad con `category_id`, `brand_id`, `name`, `slug`, `sale_mode`, `spec_chips`, `image_url`, `is_active`, `is_featured`, `sort_order`. Relaciones: 1:N con `products` (`products.line_id`), N:1 con `categories` y `brands`. |
| **products**: `line_id`, `variant_label`, `price_is_simulated`, `cost_price` | `…_100000_create_product_lines_table`, `2026_10_25_100000_add_cost_price_to_products_table` | Cuatro columnas nuevas en el diccionario. |
| Entidad nueva **quote_requests** (cotización sin cuenta, catálogo o libre) y **quote_request_attachments** | `2026_11_06_110000…`, `2026_11_10_100000…` | Entidades independientes de `orders` (visitante sin cuenta): datos de contacto, `items` (json), `type`, `status`, archivo adjunto 1:1. |
| Entidad nueva **promotions** | `2026_11_07_100000_create_promotions_table` | N:1 con `product_lines` y opcional con `products`. |
| Entidad nueva **newsletter_subscriptions** | `2026_11_08_100200…` | Correo único con consentimiento. |
| **customers** y **orders**: `type`/`customer_type` (empresa o persona natural); `company_name` y `nit` ahora nulos | `2026_10_25_120000_add_type_to_customers_table` | Corregir las dos columnas y agregar `type`. |

Total actual: 13 tablas de negocio (más las de Laravel: tokens, caché, jobs, sesiones).

## C4

- **Nivel 1 (contexto)**: sin cambios de fondo; opcional agregar al visitante sin cuenta (cotización libre).
- **Nivel 2 (contenedores)**: confirmar que aparezcan: tienda React en Cloudflare Pages (cliente externo, contenedor del sistema), Laravel en Render (panel Blade + API), MySQL en Aiven. Agregar el envío de correo (`FreeQuoteReceived`) si se muestra.
- **Nivel 3 (componentes)**: agregar `ProductLineService`/`ProductLineRepository`, `QuoteRequestService`, `PromotionService`, `NewsletterService`, `CatalogPriceService` e `InventoryService`; y los controladores `Api\V1\Admin\ProductController` y `Api\V1\Admin\CategoryController` (los dos CRUD completos de la API).

## Fase de esta entrega
- Segundo CRUD de la API: `/api/v1/admin/categories` (además de `/api/v1/admin/products`).
