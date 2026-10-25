# Imagen de producción (Render). Dos etapas: una compila los assets con Node
# y la final solo lleva PHP, el código y el resultado del build.

# ---- Etapa 1: assets (Vite) ----
FROM node:22-slim AS assets

WORKDIR /app

COPY package.json package-lock.json .npmrc ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---- Etapa 2: aplicación ----
FROM php:8.4-cli AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libonig-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        bcmath \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

# Primero solo las dependencias, para que Docker reutilice esta capa si el código cambia
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

# Varios procesos para `php artisan serve` (por defecto atiende una sola petición a la vez)
ENV PHP_CLI_SERVER_WORKERS=4

USER www-data

EXPOSE 10000

CMD ["sh", "/var/www/docker/entrypoint.sh"]
