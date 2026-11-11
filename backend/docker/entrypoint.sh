#!/bin/sh
# Arranque en Render. Orden pensado para que el PUERTO ABRA SIEMPRE, aunque las migraciones tarden:
#   1. cachés de configuración (sin base de datos),
#   2. servidor en segundo plano (Render detecta el puerto y da el despliegue por vivo),
#   3. migraciones con tiempo máximo y un log de cada una (hora de inicio y de fin),
#   4. siembra opcional, y espera al servidor.
# Si el plan de Render permite "Pre-Deploy Command", ponerlo ahí (php artisan migrate --force) y definir RUN_MIGRATIONS_ON_START=false.
log() { echo "[$(date -u +%H:%M:%S)] $*"; }

# Lo que falle aquí se ve en el log, pero el servidor arranca de todos modos
step() {
    name="$1"; shift
    log "=== $name"
    "$@"
    code=$?
    if [ "$code" -ne 0 ]; then
        log "!!! $name falló (código $code). El servidor sigue arriba; revisar el log de arriba."
        return 1
    fi
}

step "Caché de configuración" php artisan config:cache
step "Caché de rutas" php artisan route:cache
step "Caché de vistas" php artisan view:cache

log "=== Iniciando servidor en el puerto ${PORT:-10000}"
php artisan serve --no-reload --host=0.0.0.0 --port="${PORT:-10000}" &
SERVER_PID=$!
# Al apagar el contenedor, apagar también el servidor
trap 'kill -TERM "$SERVER_PID" 2>/dev/null' TERM INT

if [ "${RUN_MIGRATIONS_ON_START:-true}" = "true" ]; then
    log "=== Migraciones (máximo ${MIGRATION_TIMEOUT:-600} s)"
    timeout "${MIGRATION_TIMEOUT:-600}" php artisan migrate --force -v
    code=$?
    if [ "$code" -eq 124 ]; then
        log "!!! Las migraciones superaron ${MIGRATION_TIMEOUT:-600} s y se cortaron. La última línea 'migrando:' de arriba es la que se quedó. Las migraciones son idempotentes: un nuevo despliegue las retoma."
    elif [ "$code" -ne 0 ]; then
        log "!!! Las migraciones terminaron con error (código $code). Ver el detalle arriba."
    else
        log "=== Migraciones terminadas"
    fi
else
    log "=== Migraciones omitidas (RUN_MIGRATIONS_ON_START=false): deben correr en el Pre-Deploy Command"
fi

if [ "$SEED_ON_DEPLOY" = "true" ]; then
    step "Datos iniciales (SEED_ON_DEPLOY=true)" php artisan db:seed --force
fi

# Catálogo por líneas: solo si aún no hay líneas. SEED_CATALOG=false lo omite.
if [ "$SEED_CATALOG" != "false" ]; then
    step "Catálogo por líneas (si falta)" php artisan db:seed --class=ProductionCatalogSeeder --force
fi

log "=== Listo: el servidor atiende en el puerto ${PORT:-10000}"
wait "$SERVER_PID"
