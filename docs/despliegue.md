# Despliegue del backend (Render + Aiven MySQL)

El backend se despliega como una imagen Docker (`backend/Dockerfile`) en Render, con la base de datos MySQL en Aiven.

## Cómo funciona

- **Dockerfile** (dos etapas): una con Node compila los assets con `npm run build`; la final usa PHP 8.4 con `pdo_mysql`, `mbstring`, `bcmath`, `zip` y `opcache`, e instala Composer con `--no-dev --optimize-autoloader`.
- **`backend/docker/entrypoint.sh`** corre al iniciar el contenedor:
  1. `php artisan migrate --force`
  2. `php artisan db:seed --force`, solo si `SEED_ON_DEPLOY=true`
  3. `config:cache`, `route:cache` y `view:cache`
  4. Inicia el servidor en el puerto de `PORT` (10000 por defecto)
- **SSL a Aiven**: `config/database.php` usa el certificado `backend/docker/aiven-ca.pem` cuando `MYSQL_ATTR_SSL_CA` está definida. Es el certificado público de la CA de Aiven, no es un secreto.
- **HTTPS**: Render recibe el HTTPS y llama a la app por HTTP. `bootstrap/app.php` confía en el proxy (`trustProxies`) para que `route()`, `url()` y los assets salgan con `https://`.

## Variables de entorno en Render

Se ponen en el servicio, en **Environment**. Los valores marcados como *secreto* nunca van al repositorio.

### Aplicación

| Variable | Valor |
|---|---|
| `APP_NAME` | `Imagen Eléctrica` |
| `APP_ENV` | `production` |
| `APP_KEY` | *secreto*: genera uno con `php artisan key:generate --show` (formato `base64:...`) |
| `APP_DEBUG` | `false` |
| `APP_URL` | https://imagen-electrica-ecommerce.onrender.com|
| `APP_TIMEZONE` | `America/Bogota` |
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | `es` |
| `LOG_CHANNEL` | `stderr` (así los logs aparecen en el panel de Render) |
| `LOG_LEVEL` | `info` |

### Base de datos (datos que muestra Aiven en la pestaña de conexión)

| Variable | Valor |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | host del servicio de Aiven |
| `DB_PORT` | puerto de Aiven (no es 3306) |
| `DB_DATABASE` | nombre de la base (ej. `defaultdb`) |
| `DB_USERNAME` | usuario de Aiven |
| `DB_PASSWORD` | *secreto* |
| `MYSQL_ATTR_SSL_CA` | `docker/aiven-ca.pem` |

### Sesiones, caché y colas (driver `database`, sin Redis)

| Variable | Valor |
|---|---|
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |

### Datos iniciales (seeders)

| Variable | Valor |
|---|---|
| `SEED_ON_DEPLOY` | `false` |
| `SEED_ADMIN_EMAIL` | correo del administrador |
| `SEED_ADMIN_PASSWORD` | *secreto* |
| `SEED_COMERCIAL_EMAIL` | correo del comercial |
| `SEED_COMERCIAL_PASSWORD` | *secreto* |
| `SEED_CUSTOMER_PASSWORD` | *secreto* (solo si se carga el `DemoSeeder`) |

El seeder `CatalogSeeder` carga el catálogo por líneas: 33 líneas y 351 referencias del inventario, desde `backend/database/data/catalogo.json` (se genera con `docs/datos/generar_catalogo.py`). Los precios del Excel son **simulados** y el panel los marca con la insignia "Simulado" hasta tener los reales. Es idempotente: no pisa precio ni stock cambiados después.

### Tienda

| Variable | Valor |
|---|---|
| `SHOP_FREE_SHIPPING_FROM` | compra desde la cual el envío es gratis (COP), ej. `500000` |
| `SHOP_SHIPPING_LOCAL` | costo de envío local, ej. `15000` |
| `SHOP_SHIPPING_NATIONAL` | costo de envío nacional, ej. `35000` |

### CORS (tienda en React)

| Variable | Valor |
|---|---|
| `SANCTUM_TOKEN_EXPIRATION` | `10080` (minutos; el token de la API vence a los 7 días) |
| `CORS_ALLOWED_ORIGINS` | orígenes permitidos separados por coma, ej. `http://localhost:5173,https://tu-tienda.pages.dev` |

`PORT` lo define Render solo; no hay que ponerla.

## Pasos

1. **Aiven**: crea el servicio MySQL y copia host, puerto, base, usuario y contraseña. Si Aiven restringe IPs, permite las de Render (o déjalo abierto mientras pruebas).
2. **Render**: *New → Web Service*, conecta el repositorio de GitHub y elige la rama a desplegar.
3. Configura el servicio:
   - **Language / Runtime**: Docker
   - **Root Directory**: `backend`
   - **Dockerfile Path**: `./Dockerfile`
   - **Health Check Path**: `/up`
4. Agrega las variables de la sección anterior. En el primer despliegue pon `SEED_ON_DEPLOY=true`.
5. Despliega. El primer arranque corre las migraciones y los seeders (mira los logs).
6. Cuando termine, cambia `SEED_ON_DEPLOY` a `false` para que los siguientes despliegues no vuelvan a sembrar datos.

## Probar

- `https://<tu-servicio>.onrender.com/up` responde 200.
- `https://<tu-servicio>.onrender.com/admin/login` abre el panel con los estilos cargados y los enlaces en `https://`.
- `https://<tu-servicio>.onrender.com/api/v1/products` devuelve el catálogo en JSON.
- Documentación de la API: `https://<tu-servicio>.onrender.com/docs/index.html`.

## Notas

- El plan gratuito de Render duerme el servicio tras un rato sin tráfico; la primera petición después tarda en responder.
- Cada despliegue ejecuta `migrate --force`: las migraciones nuevas se aplican solas.
- Para probar la imagen en tu máquina: `docker build -t imagen-electrica-backend backend` y correrla con las variables de arriba (con un MySQL local).
- Las imágenes de productos son URLs públicas: no se guardan archivos en el disco del servidor (Render los borra al reiniciar).


## Actualizar el despliegue (versión con catálogo por líneas)

Si `https://<tu-servicio>.onrender.com/api/v1/families` o `/lines` responden **404** mientras `/brands` responde 200, Render tiene una versión vieja del backend.

1. Render despliega la rama que se eligió en el servicio (*Settings → Build & Deploy → Branch*). Haz que esa rama tenga el código actual: integra `feature/fase1-ui-movil` con un Pull Request, o cambia la rama del servicio mientras pruebas.
2. En *Manual Deploy* pulsa **Deploy latest commit** (o espera el auto-deploy).
3. No hace falta tocar variables: al arrancar, `entrypoint.sh` corre las migraciones (incluye `product_lines` y `quote_requests`) y `ProductionCatalogSeeder`, que carga las 33 líneas y las 351 referencias **solo si todavía no hay líneas**. Así un siguiente despliegue no recrea una línea eliminada a propósito. `SEED_CATALOG=false` lo omite y `SEED_CATALOG=force` lo vuelve a correr (idempotente: no pisa precios ni stock cambiados).
4. Verifica: `bash docs/verificar-api.sh https://<tu-servicio>.onrender.com`. Debe mostrar HTTP 200 en `families`, `lines`, `lines/w22-iec-carcasa-hierro`, `products` y `brands`, y los totales (33 líneas, 351 referencias).

Variables nuevas opcionales: `SEED_CATALOG` (`false` o `force`) y `SANCTUM_TOKEN_EXPIRATION` (minutos; por defecto 10080).


## Antes de subir migraciones o seeders: probar contra MySQL 8 en modo estricto

SQLite (las pruebas de `php artisan test`) es permisivo con los tipos; MySQL en Aiven no. Un error típico: PHP convierte las claves de array numéricas (`'10046368' => ...`) en **enteros**, y `where('sku', 10046368)` sobre la columna de texto `sku` hace que MySQL convierta todos los SKU a número y falle con `Truncated incorrect DOUBLE value` en los alfanuméricos. Reglas: los SKU siempre como **string** (en listas de tuplas, no como clave de array; `(string) $sku` en `where`/`whereIn`) y las migraciones de datos **idempotentes**, porque MySQL no revierte los cambios de datos hechos antes de un error.

Pasos (desde `backend/`; no toca tu `.env` ni tu MySQL de `docker compose`):

```bash
# 1. MySQL 8 limpio en otro puerto (modo estricto por defecto)
docker run -d --name ie_mysql_test -p 3399:3306 -e MYSQL_DATABASE=ie_test -e MYSQL_ROOT_PASSWORD=testpass mysql:8.0
export DB_CONNECTION=mysql DB_HOST=127.0.0.1 DB_PORT=3399 DB_DATABASE=ie_test DB_USERNAME=root DB_PASSWORD=testpass

# 2. Instalación nueva
php artisan migrate:fresh --seed --force

# 3. Actualización de producción: revierte las migraciones nuevas (cambia --step al número de pendientes) y vuelve a aplicarlas
php artisan migrate:rollback --step=7 --force
php artisan migrate --force

# 4. Idempotencia: seeders dos veces y, si cambiaste una migración de datos, borra su fila y córrela otra vez
php artisan db:seed --force && php artisan db:seed --force
docker exec ie_mysql_test mysql -uroot -ptestpass ie_test -e "delete from migrations where migration='<nombre_de_la_migracion>'"
php artisan migrate --force

# 5. Limpieza
docker rm -f ie_mysql_test
```

Después de desplegar: `bash docs/verificar-api.sh` (comprueba `families`, `lines`, `POST /newsletter` → 422 sin datos y que `/lines` traiga imágenes).

## Imágenes de producto recortadas (nivel 2)

La migración de datos `2026_11_09_100000_assign_cutout_product_images` apunta las líneas y **cada una de sus referencias** a las imágenes recortadas de `cliente/public/img/catalogo/`. Reemplaza solo las rutas antiguas del proyecto (`/img/productos/…`, `/img/familias/…`) y llena vacíos; una URL cambiada a mano no se pisa. Se probó contra MySQL 8 estricto (instalación nueva, actualización con rutas antiguas, URL propia respetada y segunda corrida idempotente).

**Orden de despliegue:** las rutas antiguas desaparecen del sitio estático, así que conviene desplegar la tienda (Cloudflare Pages) y el backend (Render, que corre la migración) casi a la vez; entre una y otra algunas miniaturas pueden tardar en verse. Después: `bash docs/verificar-api.sh`.
