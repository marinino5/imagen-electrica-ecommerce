<?php

namespace App\Services;

use App\Enums\DeliveryMethod;

/**
 * Tarifa de envío por zona (config/shop.php → shipping.zones). La zona sale del municipio de entrega (código DANE):
 * «local» para Bucaramanga, Floridablanca, Girón y Piedecuesta; «national» para el resto del país. Recoger en tienda no cuesta.
 */
class ShippingService
{
    /**
     * @return array{zone: string, zone_label: string, method: DeliveryMethod, cost: float}
     */
    public function zoneFor(string $cityCode): array
    {
        $departmentCode = substr($cityCode, 0, 2);

        foreach ((array) config('shop.shipping.zones') as $zone) {
            $byCity = in_array($cityCode, $zone['cities'] ?? [], true);
            $byDepartment = in_array($departmentCode, $zone['departments'] ?? [], true);
            $catchAll = ! isset($zone['cities']) && ! isset($zone['departments']);

            if ($byCity || $byDepartment || $catchAll) {
                return ['zone' => $zone['key'], 'zone_label' => $zone['label'], 'method' => DeliveryMethod::from($zone['method']), 'cost' => round((float) $zone['cost'], 2)];
            }
        }

        // Sin zonas configuradas: tarifa nacional
        return ['zone' => 'national', 'zone_label' => 'Resto del país', 'method' => DeliveryMethod::National, 'cost' => round((float) config('shop.shipping.cost.national', 0), 2)];
    }

    /**
     * Cotización de un envío: tipo, zona y valor (0 al llegar a la compra mínima para envío gratis).
     *
     * @return array{zone: string, zone_label: string, method: string, cost: float, base_cost: float, free: bool, free_from: float}
     */
    public function quote(string $cityCode, float $linesTotal): array
    {
        $zone = $this->zoneFor($cityCode);
        $freeFrom = (float) config('shop.shipping.free_shipping_from');
        $free = $linesTotal >= $freeFrom;

        return [
            'zone' => $zone['zone'], 'zone_label' => $zone['zone_label'], 'method' => $zone['method']->value,
            'cost' => $free ? 0.0 : $zone['cost'], 'base_cost' => $zone['cost'], 'free' => $free, 'free_from' => $freeFrom,
        ];
    }
}
