# E-commerce B2B Imagen Eléctrica

Tienda en línea B2B de Imagen Eléctrica, distribuidora de material eléctrico industrial en Bucaramanga. Los clientes empresariales consultan el catálogo, compran productos disponibles y solicitan cotizaciones. El equipo comercial gestiona pedidos y cotizaciones desde un panel administrativo.

## Estructura del repositorio

| Carpeta | Contenido |
|---|---|
| [`backend/`](backend/) | Aplicación Laravel 13: panel administrativo (Blade), API REST (`/api/v1`) y la lógica de negocio que comparten. |
| [`cliente/`](cliente/) | Tienda del cliente en React + Vite. Consume la API y se despliega por separado. |
| [`docs/`](docs/) | Documento de arquitectura, colección de Bruno y documentación de la API. |

## Inicio rápido (todo en tu computador)

Necesitas PHP 8.3+, Composer, Node 20+ y Docker (solo para MySQL).

```bash
# 1. Base de datos MySQL 8 local
cd backend
docker compose up -d                       # levanta MySQL (puerto 3306)

# 2. Backend: panel + API en http://localhost:8000
composer setup                             # instala, crea .env y genera APP_KEY
# pon en backend/.env la contraseña de MySQL (DB_PASSWORD) y las SEED_*_PASSWORD
php artisan migrate:fresh --seed           # tablas, usuarios, catálogo (445 referencias) y promociones
php artisan serve                          # http://localhost:8000/admin

# 3. Tienda React apuntando a ese backend (en otra terminal)
cd cliente
npm install
npm run dev:local                          # http://localhost:5173, usa cliente/.env.api-local
```

- `npm run dev` (sin `:local`) usa la API publicada en Render.
- `php artisan test` corre las pruebas del backend (usan SQLite en memoria, no tocan tu MySQL).
- `bash docs/verificar-api.sh <url>` comprueba que un servidor tiene la API actual.

Cada carpeta tiene su propio README con más detalle.
