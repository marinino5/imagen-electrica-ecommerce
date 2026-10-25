#!/bin/sh
set -e

echo "=== Ejecutando migraciones ==="
php artisan migrate --force

if [ "$SEED_ON_DEPLOY" = "true" ]; then
    echo "=== Cargando datos iniciales (SEED_ON_DEPLOY=true) ==="
    php artisan db:seed --force
fi

echo "=== Optimizando Laravel ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Iniciando servidor en el puerto ${PORT:-10000} ==="
exec php artisan serve --no-reload --host=0.0.0.0 --port="${PORT:-10000}"
