<?php

namespace App\Enums;

/**
 * Origen del precio de un producto cuando NO es el real. Nulo = precio confirmado por la empresa (o producto sin precio).
 * «referencia» es un valor de mercado en Colombia puesto mientras la empresa entrega el precio de Siigo: el panel lo marca
 * y lo deja filtrar para confirmarlo. catalog:load-prices o editar el precio en el panel lo reemplaza y lo limpia.
 */
enum PriceSource: string
{
    case Reference = 'referencia';

    public function label(): string
    {
        return match ($this) {
            self::Reference => 'Precio de referencia — confirmar con la empresa',
        };
    }
}
