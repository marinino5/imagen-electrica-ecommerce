# Despliegue del backend (Render + Aiven MySQL)

El backend se despliega como una imagen Docker (`backend/Dockerfile`) en Render, con la base de datos MySQL en Aiven.

## Cómo funciona

- **Dockerfile** (dos etapas): una con Node compila los assets con `npm run build`; la final usa PHP 8.4 con `pdo_mysql`, `mbstring`, `bcmath`, `zip` y `opcache`, e instala Composer con `--no-dev --optimize-autoloader`.
- **`backend/docker/entrypoint.sh`** corre al iniciar el contenedor, en este orden pensado para que **el puerto abra siempre**:
  1. `config:cache`, `route:cache` y `view:cache` (no usan la base de datos)
  2. Inicia el servidor en segundo plano en el puerto de `PORT` (10000 por defecto): Render ve el puerto y da el despliegue por vivo
  3. `php artisan migrate --force -v` con tiempo máximo (`MIGRATION_TIMEOUT`, 600 s). Cada migración deja en el log una línea `[hh:mm:ss UTC] migrando: …` y otra `migrada: … (n s)`; si una falla o se corta, el servidor sigue arriba y el log dice cuál fue
  4. `db:seed` solo si `SEED_ON_DEPLOY=true`, y `ProductionCatalogSeeder` (solo actúa si no hay líneas)
- **Bloqueos**: la conexión MySQL fija `lock_wait_timeout` e `innodb_lock_wait_timeout` en 60 s (`DB_LOCK_WAIT_TIMEOUT`). Sin eso, un cambio de tabla que espera un bloqueo de otra sesión puede quedarse horas en «Running migrations.».
- **Pre-Deploy Command (opcional)**: si el plan de Render lo permite, pon `php artisan migrate --force` en *Pre-Deploy Command* y agrega la variable `RUN_MIGRATIONS_ON_START=false`: así las migraciones corren **antes** de cambiar de versión y, si fallan, Render no reemplaza la versión que funciona.
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

### Arranque, stock y correo (nuevas)

| Variable | Valor |
|---|---|
| `SHOP_TRACK_STOCK` | `false` (por defecto; no hace falta ponerla). Mientras no haya Siigo el stock es 0 y no decide nada: todo producto con precio se compra. Poner `true` al conectar Siigo |
| `RUN_MIGRATIONS_ON_START` | `true` (por defecto). `false` solo si las migraciones corren en el *Pre-Deploy Command* |
| `MIGRATION_TIMEOUT` | `600` (segundos máximos de las migraciones al arrancar) |
| `DB_LOCK_WAIT_TIMEOUT` | `60` (segundos máximos esperando un bloqueo de MySQL) |
| `MAIL_MAILER` + `MAIL_*` | SMTP real (Brevo, Gmail…). Con `log` el correo de la compra de invitado solo queda en el log y la persona no recibe el enlace de su pedido |
| `FRONTEND_URL` | https://imagen-electrica.pages.dev (o el dominio de la tienda): es la base del enlace del correo del pedido de invitado |

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
| `STORE_URL` | URL de la tienda (React), por ejemplo `https://imagen-electrica.pages.dev`. A ella apunta el enlace del correo de «Recuperar contraseña» (`/restablecer?token=…`). Sin definirla usa `http://localhost:5173` |
| `MAIL_MAILER` y datos SMTP | Para que el correo de recuperación **llegue de verdad** hay que configurar un SMTP real (con `MAIL_MAILER=log` el correo solo queda en el registro). Pendiente de elegir proveedor |

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


## Si el despliegue se queda en «Running migrations.» (o «No open ports detected»)

Desde esta versión el puerto abre antes de migrar, así que «No open ports detected» ya no debería aparecer. Si las migraciones tardan o fallan:

1. En el log de Render busca la **última línea `migrando:`** sin su `migrada:`: esa es la que se colgó.
2. En Aiven (consola SQL o `mysql` con SSL) mira qué hay corriendo y bloqueando:

```sql
SHOW FULL PROCESSLIST;                                   -- sesiones; fíjate en Time grande, State «Waiting for table metadata lock» o Command «Sleep» con transacción abierta
SELECT trx_id, trx_mysql_thread_id, trx_started, trx_state, LEFT(trx_query, 80) AS consulta
FROM information_schema.innodb_trx;                      -- transacciones abiertas (una migración a medias que sigue viva aparece aquí)
SELECT * FROM sys.schema_table_lock_waits LIMIT 10;      -- quién espera a quién (si el usuario tiene acceso a sys)
SELECT id, migration, batch FROM migrations ORDER BY id DESC LIMIT 8;   -- hasta dónde llegó Laravel
```

3. Para liberar una sesión colgada (solo de los procesos del propio despliegue; **no** borra datos, MySQL deshace la transacción abierta):

```sql
KILL <Id de SHOW PROCESSLIST>;
```

4. Estado de cada migración reciente. Una migración queda **registrada** (fila en `migrations`) solo cuando termina; si se cortó, no está registrada y se vuelve a correr completa. Todas son idempotentes:

| Migración | Cómo saber si quedó aplicada | Si se cortó |
|---|---|---|
| `2026_11_12_100000_add_price_source_and_reference_prices` | `SHOW COLUMNS FROM products LIKE 'price_source'` | Se repite sin riesgo: no pisa precios reales ni de referencia ya cargados |
| `2026_11_12_120000_restructure_catalog_lines` | Fila en `migrations`; la tienda muestra 114 líneas y «Herramientas y Repuestos» | Puede dejar líneas nuevas ya creadas (131 en total mientras tanto); al repetirse deja 114 y borra las sobrantes |
| `2026_11_13_100000_create_datasheet_downloads_table` | `SHOW TABLES LIKE 'datasheet_downloads'` | Si la tabla existe se omite |
| `2026_11_14_100000_add_consent_version_and_unsubscribe_to_newsletter_subscriptions_table` | `SHOW COLUMNS FROM newsletter_subscriptions LIKE 'unsubscribed_at'` | Agrega solo la columna que falte |
| `2026_11_14_110000_migrate_legacy_categories` | Fila en `migrations` | Se repite sin riesgo |

5. Con la sesión liberada, **Manual Deploy → Deploy latest commit**. Se retoman solas las migraciones que falten.

**Por qué se colgaba:** `2026_11_12_120000` hacía unas 3.400 consultas una por una; contra Aiven (con ~100 ms de red por consulta) son varios minutos, y además corrían antes de abrir el puerto. Ahora hace unas 220 (lecturas en bloque y `UPDATE … CASE` por lotes) y el puerto abre primero.
