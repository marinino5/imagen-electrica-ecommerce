<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Enlace seguro para que quien compró sin cuenta vea el estado de su pedido.
 *
 * Es una URL firmada de Laravel (HMAC con APP_KEY) con fecha de vencimiento: quien no tiene el enlace no puede adivinar un código
 * PED-0001. En el correo va la dirección de la TIENDA (`/pedido/PED-0001?expires=…&signature=…`); la tienda reenvía esos dos
 * valores a la API, que los vuelve a verificar contra la ruta `api.v1.guest.orders.show`.
 */
final class GuestOrderLink
{
    private const ROUTE = 'api.v1.guest.orders.show';

    /** @return array{expires: int, signature: string} */
    public static function make(string $code): array
    {
        $url = URL::temporarySignedRoute(self::ROUTE, now()->addDays((int) config('shop.guest_link_days', 60)), ['code' => $code]);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return ['expires' => (int) $query['expires'], 'signature' => (string) $query['signature']];
    }

    /** Dirección que va en el correo: la página de estado de la tienda con la firma. */
    public static function storefrontUrl(string $code): string
    {
        return config('shop.frontend_url').'/pedido/'.rawurlencode($code).'?'.http_build_query(self::make($code));
    }

    /** ¿La firma es de este código y todavía no venció? */
    public static function isValid(string $code, mixed $expires, mixed $signature): bool
    {
        if (! is_scalar($expires) || ! is_string($signature) || $signature === '') {
            return false;
        }

        $url = URL::route(self::ROUTE, ['code' => $code, 'expires' => $expires, 'signature' => $signature]);

        return URL::hasValidSignature(Request::create($url));
    }
}
