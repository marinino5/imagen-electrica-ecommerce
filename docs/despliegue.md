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

### Tienda

| Variable | Valor |
|---|---|
| `SHOP_FREE_SHIPPING_FROM` | compra desde la cual el envío es gratis (COP), ej. `500000` |
| `SHOP_SHIPPING_LOCAL` | costo de envío local, ej. `15000` |
| `SHOP_SHIPPING_NATIONAL` | costo de envío nacional, ej. `35000` |

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
