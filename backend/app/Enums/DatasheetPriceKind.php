<?php

namespace App\Enums;

/** Qué precio llevaba el PDF de la ficha: el de venta directa, uno de referencia por confirmar, o ninguno (solo cotización). */
enum DatasheetPriceKind: string
{
    case Direct = 'direct';
    case Reference = 'reference';
    case Quote = 'quote';

    public function label(): string
    {
        return match ($this) {
            self::Direct => 'Precio de venta',
            self::Reference => 'Precio de referencia',
            self::Quote => 'Solo cotización',
        };
    }
}
