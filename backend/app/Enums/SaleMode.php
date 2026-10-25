<?php

namespace App\Enums;

/**
 * Política comercial del producto. No cambia cuando el stock llega a cero:
 * la disponibilidad se calcula aparte con el stock.
 */
enum SaleMode: string
{
    case Direct = 'direct';
    case Quote = 'quote';
    case OnRequest = 'on_request';

    public function label(): string
    {
        return match ($this) {
            self::Direct => 'Venta directa',
            self::Quote => 'Solo cotización',
            self::OnRequest => 'Bajo pedido',
        };
    }
}
