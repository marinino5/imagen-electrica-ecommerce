<?php

// Reglas comerciales que cambian sin tocar código. Se ajustan desde el .env.
return [

    /*
    | Dirección pública de la tienda (Cloudflare Pages): de ahí salen las imágenes de producto y el enlace de cotización del PDF.
    */
    'frontend_url' => rtrim((string) env('FRONTEND_URL', 'https://imagen-electrica.pages.dev'), '/'),

    // Versión de la política de tratamiento de datos que acepta quien compra (se guarda con cada pedido, junto con la fecha).
    // Si el texto de /politica-datos cambia, sube la versión.
    'privacy_policy_version' => '2026-11-v1',

    // Días que vale el enlace del correo para ver el estado de un pedido hecho sin cuenta
    'guest_link_days' => 60,

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

        /*
        | Tarifas por zona (COP, IVA incluido). Se revisan en orden y gana la primera que coincide con el municipio (`cities`, código DANE de
        | 5 dígitos) o con el departamento (`departments`, 2 dígitos); la última, sin filtros, es el resto del país. `method` es el tipo de
        | envío que se guarda en el pedido: «local» (Bucaramanga, Floridablanca, Girón y Piedecuesta) o «national» (todo lo demás).
        | Para cambiar una tarifa o agregar una zona, se edita esta tabla (o las variables de entorno de cada costo).
        */
        'zones' => [
            ['key' => 'local', 'method' => 'local', 'label' => 'Bucaramanga y área metropolitana', 'cities' => ['68001', '68276', '68307', '68547'], 'cost' => (float) env('SHOP_SHIPPING_LOCAL', 15000)],
            ['key' => 'santander', 'method' => 'national', 'label' => 'Resto de Santander', 'departments' => ['68'], 'cost' => (float) env('SHOP_SHIPPING_SANTANDER', 25000)],
            ['key' => 'national', 'method' => 'national', 'label' => 'Resto del país', 'cost' => (float) env('SHOP_SHIPPING_NATIONAL', 35000)],
        ],
    ],

];
