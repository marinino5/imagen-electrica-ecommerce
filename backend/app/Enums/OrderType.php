<?php

namespace App\Enums;

enum OrderType: string
{
    case Purchase = 'purchase';
    case Quote = 'quote';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Pedido',
            self::Quote => 'Cotización',
        };
    }

    /** Plural para filtros y títulos: Pedidos, Cotizaciones (no se arma con «s» al final). */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Purchase => 'Pedidos',
            self::Quote => 'Cotizaciones',
        };
    }

    /**
     * Prefijo del código: PED-0001 para pedidos, COT-0001 para cotizaciones.
     */
    public function codePrefix(): string
    {
        return match ($this) {
            self::Purchase => 'PED',
            self::Quote => 'COT',
        };
    }
}
