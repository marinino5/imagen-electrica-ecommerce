<?php

// Reglas comerciales que cambian sin tocar código. Se ajustan desde el .env.
return [

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
