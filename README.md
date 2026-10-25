# E-commerce B2B Imagen Eléctrica

Tienda en línea B2B de Imagen Eléctrica, distribuidora de material eléctrico industrial en Bucaramanga. Los clientes empresariales consultan el catálogo, compran productos disponibles y solicitan cotizaciones. El equipo comercial gestiona pedidos y cotizaciones desde un panel administrativo.

## Estructura del repositorio

| Carpeta | Contenido |
|---|---|
| [`backend/`](backend/) | Aplicación Laravel 13: panel administrativo (Blade), API REST (`/api/v1`) y la lógica de negocio que comparten. |
| [`cliente/`](cliente/) | Tienda del cliente en React + Vite. Consume la API y se despliega por separado. |
| [`docs/`](docs/) | Documento de arquitectura, colección de Bruno y documentación de la API. |

## Inicio rápido

```bash
cd backend
composer setup
php artisan serve
```

Cada carpeta tiene su propio README con más detalle.
