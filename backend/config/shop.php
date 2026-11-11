<?php

// Reglas comerciales que cambian sin tocar código. Se ajustan desde el .env.
return [

    /*
    | Dirección pública de la tienda (Cloudflare Pages): de ahí salen las imágenes de producto y el enlace de cotización del PDF.
    */
    'frontend_url' => rtrim((string) env('FRONTEND_URL', 'https://imagen-electrica.pages.dev'), '/'),

    // Versión del texto de consentimiento del boletín (el del pie de la tienda). Si el texto cambia, sube la versión.
    'newsletter_consent_version' => '2026-11-v1',

    // Días que vale la ficha con cotización
    'datasheet_valid_days' => 15,

    /*
    | Envío (COP, IVA incluido). Recoger en tienda siempre vale 0.
    | Si el valor de los productos llega a "free_shipping_from", el envío es gratis.
    */
    'shipping' => [
        'free_shipping_from' => (float) env('SHOP_FREE_SHIPPING_FROM', 500000),
        'cost' => [
            'local' => (float) env('SHOP_SHIPPING_LOCAL', 15000),
            'national' => (float) env('SHOP_SHIPPING_NATIONAL', 35000),
        ],
    ],

];
