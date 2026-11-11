<?php

namespace App\Services;

use App\Enums\CustomerType;
use App\Enums\DeliveryMethod;
use App\Enums\FiscalCode;
use App\Enums\IdType;
use App\Mail\GuestOrderReceived;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use App\Support\Divipola;
use App\Support\GuestOrderLink;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * La compra: entrega, datos de contacto y (opcional) facturación electrónica. Sirve para un invitado y para quien tiene cuenta.
 *
 * Las reglas de pedido y cotización, el stock y los totales siguen siendo de OrderService; aquí se arma «quién compra, adónde se entrega
 * y a nombre de quién se factura» en columnas propias de `orders` con los nombres de la API de clientes de Siigo (docs/siigo-mapeo.md),
 * se guarda el perfil de quien tiene cuenta y se avisa por correo al invitado.
 */
class CheckoutService
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly ShippingService $shipping,
        private readonly CustomerRepository $customers,
        private readonly UserRepository $users,
    ) {}

    /**
     * @param  array<string, mixed>  $data  datos validados de StoreCheckoutRequest
     * @return array{orders: Collection<int, Order>, guest: bool, account_exists: bool, status_urls: array<string, string>}
     */
    public function place(?User $user, array $data): array
    {
        $customer = $user?->customer;
        $guest = $customer === null;
        $identity = $this->identity($data, $guest);

        $orders = $this->orders->createFromCart($customer, [
            'items' => $data['items'],
            'delivery_method' => $identity['delivery_method'],
            'notes' => $data['notes'] ?? null,
        ], $identity);

        if ($customer && ($data['save_to_profile'] ?? true)) {
            $this->saveProfile($customer, $data);
        }

        if ($guest) {
            $this->notifyGuest($orders);
        }

        return [
            'orders' => $orders,
            'guest' => $guest,
            // Si el correo ya tiene cuenta, la tienda ofrece iniciar sesión (sin bloquear la compra)
            'account_exists' => $guest && $this->users->findByEmail($identity['email']) !== null,
            // Mismo enlace seguro que va en el correo, para el botón «Ver el estado» de la pantalla de confirmación
            'status_urls' => $guest ? $orders->mapWithKeys(fn (Order $o) => [$o->code => GuestOrderLink::storefrontUrl($o->code)])->all() : [],
        ];
    }

    /**
     * Las columnas de `orders` con los datos del comprador, la entrega y (si la pidió) la factura. `delivery_method` es el tipo de envío
     * que resultó del municipio: pickup, local o national.
     *
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    public function identity(array $d, bool $guest): array
    {
        $d = $this->withOptionals($d);
        $invoice = (bool) $d['needs_invoice'];
        $company = $invoice && $d['person_type'] === 'Company';
        $delivery = $this->delivery($d);

        $columns = [
            'is_guest' => $guest,
            'needs_invoice' => $invoice,
            'customer_type' => $company ? CustomerType::Company : CustomerType::Natural,
            'company_name' => $company ? $d['company_name'] : null,
            'nit' => $invoice ? $d['identification'].($company ? '-'.$d['check_digit'] : '') : null,
            'contact_name' => $d['name'],
            'email' => $d['email'],
            'phone' => $d['phone'],
            'city' => null,
            ...$delivery['columns'],
            'privacy_accepted_at' => now(),
            'privacy_version' => config('shop.privacy_policy_version'),
        ];

        if ($invoice) {
            $billing = $this->billing($d, $delivery);
            $columns = [...$columns, ...$billing['columns']];
            $columns['city'] = $billing['city'];
        } else {
            $columns['city'] = $delivery['city'];
        }

        return $columns + ['delivery_method' => $delivery['method']->value];
    }

    /**
     * Entrega en columnas separadas (para calcular el envío). Recoger en tienda no lleva dirección.
     *
     * @param  array<string, mixed>  $d
     * @return array{method: DeliveryMethod, columns: array<string, mixed>, city: ?string, state: ?array, cityRow: ?array, street: ?string}
     */
    private function delivery(array $d): array
    {
        $empty = [
            'address' => null, 'delivery_state_code' => null, 'delivery_department' => null, 'delivery_city_code' => null, 'delivery_city' => null,
            'delivery_neighborhood' => null, 'delivery_road' => null, 'delivery_number' => null, 'delivery_complement' => null,
            'delivery_postal_code' => null, 'delivery_notes' => null,
        ];

        if ($d['delivery_method'] === 'pickup') {
            return ['method' => DeliveryMethod::Pickup, 'columns' => $empty, 'city' => null, 'state' => null, 'cityRow' => null, 'street' => null];
        }

        $state = Divipola::department($d['delivery_state_code']);
        $city = Divipola::city($d['delivery_state_code'], $d['delivery_city_code']);
        $zone = $this->shipping->zoneFor($city['code']);
        $street = trim(implode(', ', array_filter([trim($d['delivery_road'].' '.$d['delivery_number']), $d['delivery_complement']])));

        return [
            'method' => $zone['method'],
            'city' => $city['name'],
            'state' => $state,
            'cityRow' => $city,
            'street' => $street,
            'columns' => [
                // «Carrera 27 # 34-12, Local 3 · Barrio Cabecera · Bucaramanga, Santander»: la línea que lee un humano en el panel y en el correo
                'address' => implode(' · ', array_filter([$street, 'Barrio '.$d['delivery_neighborhood'], $city['name'].', '.$state['name']])),
                'delivery_state_code' => $state['code'], 'delivery_department' => $state['name'],
                'delivery_city_code' => $city['code'], 'delivery_city' => $city['name'],
                'delivery_neighborhood' => $d['delivery_neighborhood'], 'delivery_road' => $d['delivery_road'], 'delivery_number' => $d['delivery_number'],
                'delivery_complement' => $d['delivery_complement'], 'delivery_postal_code' => $d['delivery_postal_code'], 'delivery_notes' => $d['delivery_notes'],
            ],
        ];
    }

    /**
     * Columnas de facturación con los nombres de la API de clientes de Siigo. «Usar la misma dirección de entrega» copia el departamento,
     * el municipio, el código postal y la dirección.
     *
     * @param  array<string, mixed>  $d
     * @param  array<string, mixed>  $delivery
     * @return array{columns: array<string, mixed>, city: ?string}
     */
    private function billing(array $d, array $delivery): array
    {
        $company = $d['person_type'] === 'Company';
        $same = $d['delivery_method'] !== 'pickup' && (bool) $d['billing_same_as_delivery'];

        if ($same) {
            [$state, $city, $address, $postal] = [$delivery['state'], $delivery['cityRow'], $delivery['street'], $d['delivery_postal_code']];
        } else {
            $state = Divipola::department($d['billing_state_code']);
            $city = Divipola::city($d['billing_state_code'], $d['billing_city_code']);
            [$address, $postal] = [$d['billing_address'], $d['billing_postal_code']];
        }

        // Contacto: en una empresa, la persona que da los datos (por defecto, quien compra); en una persona, ella misma
        [$contactFirst, $contactLast] = $company
            ? ($d['contact_first_name'] ? [$d['contact_first_name'], (string) $d['contact_last_name']] : $this->splitName($d['name']))
            : [$d['first_name'], $d['last_name']];

        return [
            'city' => $city['name'],
            'columns' => [
                'billing_person_type' => $d['person_type'],
                'billing_id_type' => $company ? IdType::NIT : IdType::from($d['id_type']),
                'billing_identification' => $d['identification'],
                'billing_check_digit' => $company ? $d['check_digit'] : null,
                'billing_first_name' => $company ? null : $d['first_name'],
                'billing_last_name' => $company ? null : $d['last_name'],
                'billing_commercial_name' => $d['commercial_name'],
                'billing_vat_responsible' => (bool) $d['vat_responsible'],
                'billing_fiscal_responsibilities' => array_values(array_unique($d['fiscal_responsibilities'])),
                'billing_email' => $d['billing_email'],
                'billing_address' => $address,
                'billing_country_code' => 'CO',
                'billing_state_code' => $state['code'],
                'billing_department' => $state['name'],
                'billing_city_code' => $city['code'],
                'billing_postal_code' => $postal,
                'billing_contact_first_name' => mb_substr($contactFirst, 0, 50),
                'billing_contact_last_name' => $contactLast !== '' ? mb_substr($contactLast, 0, 50) : null,
            ],
        ];
    }

    /**
     * Guarda en el perfil lo que dio el comprador (contacto, factura y la dirección de entrega) para autocompletar la próxima compra. Si el NIT o la
     * cédula ya pertenece a otra cuenta, ese dato no se toca (no es motivo para fallar una compra).
     *
     * @param  array<string, mixed>  $d
     */
    private function saveProfile(Customer $customer, array $d): void
    {
        $d = $this->withOptionals($d);
        $changes = $this->customerChanges($d, $customer->id);

        if ($d['delivery_method'] !== 'pickup') {
            $changes['delivery_addresses'] = $this->rememberAddress($customer, $d);
        }

        $this->customers->update($customer, $changes);
    }

    /**
     * Columnas del perfil del cliente a partir de los datos de contacto y (si los hay) de facturación. Lo usan el pago, crear la cuenta y
     * «Mis datos»: un solo lugar que traduce los nombres de la API de Siigo a la ficha del cliente.
     *
     * @param  array<string, mixed>  $d  nombre, celular y, si `needs_invoice`, los campos de la factura
     * @return array<string, mixed>
     */
    public function customerChanges(array $d, ?int $customerId = null): array
    {
        $d = $this->withOptionals($d) + ['delivery_method' => 'pickup'];
        $changes = ['contact_name' => $d['name'], 'phone' => $d['phone']];

        if (! $d['needs_invoice']) {
            return $changes;
        }

        $billing = $this->billing($d, $this->delivery($d))['columns'];
        $company = $d['person_type'] === 'Company';
        $nit = $d['identification'].($company ? '-'.$d['check_digit'] : '');

        $changes += [
            'type' => $company ? CustomerType::Company : CustomerType::Natural,
            'company_name' => $company ? $d['company_name'] : null,
            'id_type' => $billing['billing_id_type'], 'check_digit' => $billing['billing_check_digit'],
            'first_name' => $billing['billing_first_name'], 'last_name' => $billing['billing_last_name'],
            'commercial_name' => $billing['billing_commercial_name'], 'vat_responsible' => $billing['billing_vat_responsible'],
            'fiscal_responsibilities' => $billing['billing_fiscal_responsibilities'], 'billing_email' => $billing['billing_email'],
            'address' => $billing['billing_address'], 'city' => Divipola::city($billing['billing_state_code'], $billing['billing_city_code'])['name'],
            'state_code' => $billing['billing_state_code'], 'department' => $billing['billing_department'],
            'city_code' => $billing['billing_city_code'], 'postal_code' => $billing['billing_postal_code'],
        ];

        if (! $this->customers->nitTakenByAnother($nit, $customerId)) {
            $changes['nit'] = $nit;
        }

        return $changes;
    }

    /** @return list<array<string, mixed>> la dirección usada primero, sin repetirla, hasta 5 */
    private function rememberAddress(Customer $customer, array $d): array
    {
        $new = [
            'state_code' => $d['delivery_state_code'], 'city_code' => $d['delivery_city_code'], 'neighborhood' => $d['delivery_neighborhood'],
            'road' => $d['delivery_road'], 'number' => $d['delivery_number'], 'complement' => $d['delivery_complement'],
            'postal_code' => $d['delivery_postal_code'], 'notes' => $d['delivery_notes'],
        ];
        $key = fn (array $a) => mb_strtolower(($a['city_code'] ?? '').'|'.($a['road'] ?? '').'|'.($a['number'] ?? '').'|'.($a['complement'] ?? ''));
        $others = array_values(array_filter((array) $customer->delivery_addresses, fn ($a) => $key($a) !== $key($new)));

        return array_slice([$new, ...$others], 0, 5);
    }

    /** «Laura Gómez Pérez» → [Laura, Gómez Pérez]; con cuatro palabras o más, los dos primeros son los nombres. @return array{0: string, 1: string} */
    private function splitName(string $full): array
    {
        $words = preg_split('/\s+/', trim($full)) ?: [];
        $cut = count($words) >= 4 ? 2 : 1;

        return [implode(' ', array_slice($words, 0, $cut)), implode(' ', array_slice($words, $cut))];
    }

    /**
     * Los campos que solo aplican a una opción pueden no llegar (la tienda no los envía): se completan con nulo o con su valor por defecto.
     *
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    private function withOptionals(array $d): array
    {
        return $d + array_fill_keys([
            'person_type', 'id_type', 'identification', 'check_digit', 'first_name', 'last_name', 'company_name', 'commercial_name', 'billing_email',
            'billing_address', 'billing_state_code', 'billing_city_code', 'billing_postal_code', 'contact_first_name', 'contact_last_name',
            'delivery_state_code', 'delivery_city_code', 'delivery_neighborhood', 'delivery_road', 'delivery_number', 'delivery_complement',
            'delivery_postal_code', 'delivery_notes',
        ], null) + [
            'needs_invoice' => false, 'vat_responsible' => false, 'billing_same_as_delivery' => false, 'fiscal_responsibilities' => [FiscalCode::NotApplicable->value],
        ];
    }

    /** @param  Collection<int, Order>  $orders */
    private function notifyGuest(Collection $orders): void
    {
        try {
            Mail::to($orders->first()->email)->send(new GuestOrderReceived($orders));
        } catch (\Throwable $e) {
            // La compra ya quedó guardada: si el correo falla solo se registra (el comercial la ve en el panel)
            Log::error('No se pudo enviar el correo del pedido de invitado '.$orders->pluck('code')->implode(', ').': '.$e->getMessage());
        }
    }
}
