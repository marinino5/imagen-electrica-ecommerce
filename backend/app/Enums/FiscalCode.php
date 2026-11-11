<?php

namespace App\Enums;

/** Responsabilidad fiscal: los códigos de `fiscal_responsibilities` de la API de clientes de Siigo. */
enum FiscalCode: string
{
    case NotApplicable = 'R-99-PN';
    case LargeTaxpayer = 'O-13';
    case SelfWithholder = 'O-15';
    case VatWithholdingAgent = 'O-23';
    case SimpleRegime = 'O-47';

    public function label(): string
    {
        return match ($this) {
            self::NotApplicable => 'No aplica / otros',
            self::LargeTaxpayer => 'Gran contribuyente',
            self::SelfWithholder => 'Autorretenedor',
            self::VatWithholdingAgent => 'Agente de retención de IVA',
            self::SimpleRegime => 'Régimen simple de tributación',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
