<?php

return [

    /*
    | CORS: la tienda (React) vive en otro dominio y llama a la API desde el navegador.
    | Solo se permiten las rutas de la API y los orígenes de CORS_ALLOWED_ORIGINS
    | (separados por coma): el servidor de Vite en local y el dominio de Cloudflare Pages.
    | Se usa token Bearer, no cookies, así que no hace falta supports_credentials.
    */
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173'))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
