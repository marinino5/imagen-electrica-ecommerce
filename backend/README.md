# Backend — Laravel 13

Panel administrativo (Blade) y API REST de Imagen Eléctrica. Ambos usan los mismos Services y Repositories.

## Requisitos

- PHP 8.3 o superior
- Composer
- Node.js y npm (para compilar los assets con Vite)

## Instalación

Desde esta carpeta (`backend/`):

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

También puedes hacer todo en un paso con `composer setup`.

## Uso

```bash
php artisan serve   # http://127.0.0.1:8000
php artisan test    # pruebas
```

## API REST (`/api/v1`)

Autenticación con token de Sanctum (`Authorization: Bearer ...`). El catálogo es público; el resto exige token. Los errores salen en JSON y en español (`401`, `403`, `404`, `422`, `429`).

| Quién | Rutas |
|---|---|
| Público | `GET categories`, `GET brands`, `GET products` (filtros `q`, `category`, `brand`, `sale_mode`, `featured`, `in_stock`, `sort`, `per_page`), `GET products/{slug}` |
| Sesión | `POST register`, `POST login` (5 por minuto), `POST logout`, `GET me` |
| Cliente | `PUT me`, `GET/POST orders`, `GET orders/{code}`, `PATCH orders/{code}/cancel` |
| Administrador | `GET/POST admin/products`, `GET/PUT/DELETE admin/products/{id}` |

Costo de envío y compra mínima para envío gratis: variables `SHOP_*` del `.env` (`config/shop.php`).

### Documentación y pruebas

```bash
composer docs:api     # genera la documentación (Scribe) en public/docs y copia collection.json y openapi.yaml a ../docs
php artisan test      # incluye las pruebas de la API
```

La documentación queda en `/docs` del servidor (`public/docs/index.html`). Para probar a mano, abre la carpeta `docs/bruno` como colección en Bruno, elige el entorno `Local`, escribe el secreto `admin_password` (el `SEED_ADMIN_PASSWORD` de tu `.env`) y ejecuta las carpetas en orden.
