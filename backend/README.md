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
