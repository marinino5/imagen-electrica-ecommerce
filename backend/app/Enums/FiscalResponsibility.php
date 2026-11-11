<?php

namespace App\Enums;

/** Responsabilidad fiscal del comprador (RUT, casilla 53). */
enum FiscalResponsibility: string
{
    case VatResponsible = 'vat_responsible';
    case NotVatResponsible = 'not_vat_responsible';
    case LargeTaxpayer = 'large_taxpayer';
    case SelfWithholder = 'self_withholder';
    case SimpleRegime = 'simple_regime';

    public function label(): string
    {
        return match ($this) {
            self::VatResponsible => 'Responsable de IVA',
            self::NotVatResponsible => 'No responsable de IVA',
            self::LargeTaxpayer => 'Gran contribuyente',
            self::SelfWithholder => 'Autorretenedor',
            self::SimpleRegime => 'Régimen simple de tributación',
        };
    }

    /** Código de la responsabilidad en el RUT (casilla 53). Siigo usa su propio formato de código: confirmarlo al integrar (docs/siigo-mapeo.md). */
    public function dianCode(): string
    {
        return match ($this) {
            self::VatResponsible => '48',
            self::NotVatResponsible => '49',
            self::LargeTaxpayer => '13',
            self::SelfWithholder => '15',
            self::SimpleRegime => '47',
        };
    }
}
