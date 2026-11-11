<?php

namespace App\Support;

use App\Enums\IdType;

/**
 * Cómo se muestra el documento de un cliente en el panel: «CC 1097490864» o «NIT 900123456-7», con la etiqueta de persona natural o empresa.
 * Usa el tipo de documento real (id_type de Siigo) cuando existe; en registros antiguos, que solo guardaban un campo «nit» para todo, lo deduce
 * del tipo de cliente (empresa → NIT, persona natural → cédula) y, si tampoco lo hay, de la forma del número (9 dígitos → NIT).
 */
final class Document
{
    /**
     * @param  string|null  $customerType  `company` | `natural`
     * @return array{short: string, number: string, full: string, kind: string, is_company: bool}|null  null si no hay número
     */
    public static function describe(IdType|string|null $idType, ?string $number, ?string $checkDigit = null, ?string $customerType = null): ?array
    {
        $number = trim((string) $number);
        if ($number === '') {
            return null;
        }

        $type = $idType instanceof IdType ? $idType : ($idType ? IdType::tryFrom($idType) : null);
        $digits = Nit::digits($number);

        $type ??= match (true) {
            $customerType === 'company' => IdType::NIT,
            $customerType === 'natural' => IdType::CC,
            strlen($digits) === 9 || str_contains($number, '-') => IdType::NIT,
            default => IdType::CC,
        };

        if ($type === IdType::NIT) {
            // El número guardado puede traer ya el dígito de verificación («900123456-7»); si no, se completa
            $base = str_contains($number, '-') ? Nit::digits(explode('-', $number)[0]) : $digits;
            $dv = str_contains($number, '-') ? Nit::digits(explode('-', $number)[1] ?? '') : ($checkDigit ?? '');
            $dv = $dv !== '' ? $dv : (string) Nit::checkDigit($base);
            $number = $base.'-'.$dv;
        }

        return [
            'short' => $type->short(),
            'number' => $number,
            'full' => $type->short().' '.$number,
            'kind' => $type === IdType::NIT ? 'Empresa' : 'Persona natural',
            'is_company' => $type === IdType::NIT,
        ];
    }
}
