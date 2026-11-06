#!/bin/sh
set -e

echo "=== Ejecutando migraciones ==="
php artisan migrate --force

if [ "$SEED_ON_DEPLOY" = "true" ]; then
    echo "=== Cargando datos iniciales (SEED_ON_DEPLOY=true) ==="
    php artisan db:seed --force
fi

# Catálogo por líneas (33 líneas y 351 referencias): solo si aún no hay líneas. SEED_CATALOG=false lo omite.
if [ "$SEED_CATALOG" != "false" ]; then
    echo "=== Cargando el catálogo por líneas (si falta) ==="
    php artisan db:seed --class=ProductionCatalogSeeder --force
fi

echo "=== Optimizando Laravel ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Iniciando servidor en el puerto ${PORT:-10000} ==="
exec php artisan serve --no-reload --host=0.0.0.0 --port="${PORT:-10000}"
