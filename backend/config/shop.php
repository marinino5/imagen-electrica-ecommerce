<?php

// Reglas comerciales que cambian sin tocar código. Se ajustan desde el .env.
return [

    /*
    | Dirección pública de la tienda (Cloudflare Pages): de ahí salen las imágenes de producto y el enlace de cotización del PDF.
    */
    'frontend_url' => rtrim((string) env('FRONTEND_URL', 'https://imagen-electrica.pages.dev'), '/'),

    // Versión del texto de consentimiento del boletín (el del pie de la tienda). Si el texto cambia, sube la versión.
    'newsletter_consent_version' => '2026-11-v1',

    /*
    | ¿El stock de la tienda es confiable? Mientras no esté conectado con Siigo, todo el inventario está en 0 y NO se usa para decidir
    | nada: todo producto de venta directa con precio se puede comprar, y el pago no descuenta unidades. Al conectar Siigo se activa
    | con SHOP_TRACK_STOCK=true y vuelven las reglas de inventario (pedido solo con stock suficiente, 422 si falta, descuento al pagar).
    */
    'track_stock' => filter_var(env('SHOP_TRACK_STOCK', false), FILTER_VALIDATE_BOOL),

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
