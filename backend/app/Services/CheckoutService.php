<?php

namespace App\Services;

use App\Enums\CustomerType;
use App\Enums\DeliveryMethod;
use App\Enums\DocumentType;
use App\Enums\FiscalResponsibility;
use App\Mail\GuestOrderReceived;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use App\Support\Divipola;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * La compra con datos de facturación electrónica: sirve para un invitado (sin cuenta) y para quien tiene cuenta.
 *
 * Las reglas de pedido y cotización, el stock y los totales siguen siendo de OrderService; aquí solo se arma «quién compra y adónde
 * se entrega» (columnas propias de `orders`, listas para mapear a Siigo: docs/siigo-mapeo.md), se guarda el perfil de quien tiene
 * cuenta y se avisa por correo al invitado.
 */
class CheckoutService
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CustomerRepository $customers,
        private readonly UserRepository $users,
    ) {}

    /**
     * @param  array<string, mixed>  $data  datos validados de StoreCheckoutRequest
     * @return array{orders: Collection<int, Order>, guest: bool, account_exists: bool}
     */
    public function place(?User $user, array $data): array
    {
        $customer = $user?->customer;
        $guest = $customer === null;
        $identity = $this->identity($data, $guest);

        $orders = $this->orders->createFromCart($customer, [
            'items' => $data['items'],
            'delivery_method' => $data['delivery_method'],
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
        ];
    }

    /**
     * Las columnas de `orders` con los datos del comprador y de la entrega.
     *
     * @param  array<string, mixed>  $d
     * @return array<string, mixed>
     */
    public function identity(array $d, bool $guest): array
    {
        $company = $d['person_type'] === CustomerType::Company->value;
        $department = Divipola::department($d['department_code']);
        $city = Divipola::city($d['department_code'], $d['city_code']);
        $fullName = trim($d['first_name'].' '.$d['last_name']);
        $method = DeliveryMethod::from($d['delivery_method']);
        $delivery = $this->delivery($d, $method, $department, $city);

        return [
            'is_guest' => $guest,
            'customer_type' => $company ? CustomerType::Company : CustomerType::Natural,
            'company_name' => $company ? $d['company_name'] : null,
            'nit' => $company ? $d['doc_number'].'-'.$d['nit_dv'] : $d['doc_number'],
            'contact_name' => $company ? $d['contact_name'] : $fullName,
            'email' => $d['billing_email'],
            'phone' => $d['phone'],
            'city' => $city['name'],
            // Facturación electrónica (columnas listas para Siigo)
            'billing_doc_type' => DocumentType::from($company ? DocumentType::NIT->value : $d['doc_type']),
            'billing_doc_number' => $d['doc_number'],
            'billing_doc_dv' => $company ? $d['nit_dv'] : null,
            'billing_first_name' => $company ? null : $d['first_name'],
            'billing_last_name' => $company ? null : $d['last_name'],
            'fiscal_responsibility' => $company ? $d['fiscal_responsibility'] : FiscalResponsibility::NotVatResponsible->value,
            'billing_email' => $d['billing_email'],
            'billing_address' => $d['billing_address'],
            'billing_department_code' => $department['code'],
            'billing_department' => $department['name'],
            'billing_city_code' => $city['code'],
            ...$delivery,
            'privacy_accepted_at' => now(),
            'privacy_version' => config('shop.privacy_policy_version'),
        ];
    }

    /**
     * Entrega en columnas separadas (para calcular el envío después). Recoger en tienda no lleva dirección;
     * «igual a la de facturación» copia el departamento, el municipio y la dirección de facturación.
     *
     * @param  array<string, mixed>  $d
     * @param  array{code: string, name: string}  $billingDepartment
     * @param  array{code: string, name: string}  $billingCity
     * @return array<string, mixed>
     */
    private function delivery(array $d, DeliveryMethod $method, array $billingDepartment, array $billingCity): array
    {
        $empty = [
            'address' => null, 'delivery_same_as_billing' => false,
            'delivery_department_code' => null, 'delivery_department' => null, 'delivery_city_code' => null, 'delivery_city' => null,
            'delivery_neighborhood' => null, 'delivery_road' => null, 'delivery_number' => null, 'delivery_complement' => null,
            'delivery_postal_code' => null, 'delivery_notes' => null,
        ];

        if ($method === DeliveryMethod::Pickup) {
            return $empty;
        }

        $same = (bool) ($d['delivery_same_as_billing'] ?? false);
        $department = $same ? $billingDepartment : Divipola::department($d['delivery_department_code']);
        $city = $same ? $billingCity : Divipola::city($d['delivery_department_code'], $d['delivery_city_code']);

        $fields = [
            'delivery_department_code' => $department['code'],
            'delivery_department' => $department['name'],
            'delivery_city_code' => $city['code'],
            'delivery_city' => $city['name'],
            // Igual a facturación: la dirección completa va en «vía»; el barrio y el número no se piden
            'delivery_neighborhood' => $same ? null : $d['delivery_neighborhood'],
            'delivery_road' => $same ? $d['billing_address'] : $d['delivery_road'],
            'delivery_number' => $same ? null : $d['delivery_number'],
            'delivery_complement' => $same ? null : ($d['delivery_complement'] ?? null),
            'delivery_postal_code' => $d['delivery_postal_code'] ?? null,
            'delivery_notes' => $d['delivery_notes'] ?? null,
        ];

        // «Carrera 27 # 34-12, Local 3 · Barrio Cabecera · Bucaramanga, Santander»: la línea que lee un humano en el panel y en el correo
        $street = trim(implode(', ', array_filter([trim(($fields['delivery_road'] ?? '').' '.($fields['delivery_number'] ?? '')), $fields['delivery_complement']])));
        $address = implode(' · ', array_filter([
            $street,
            $fields['delivery_neighborhood'] ? 'Barrio '.$fields['delivery_neighborhood'] : null,
            $fields['delivery_city'].', '.$fields['delivery_department'],
        ]));

        return ['address' => $address, 'delivery_same_as_billing' => $same, ...$fields];
    }

    /**
     * Guarda los datos de facturación en el perfil para autocompletar la próxima compra. Si el NIT o la cédula ya
     * pertenece a otra cuenta, ese dato no se toca (no es motivo para fallar una compra).
     *
     * @param  array<string, mixed>  $d
     */
    private function saveProfile(Customer $customer, array $d): void
    {
        $company = $d['person_type'] === CustomerType::Company->value;
        $department = Divipola::department($d['department_code']);
        $city = Divipola::city($d['department_code'], $d['city_code']);
        $nit = $company ? $d['doc_number'].'-'.$d['nit_dv'] : $d['doc_number'];

        $changes = [
            'type' => $company ? CustomerType::Company : CustomerType::Natural,
            'company_name' => $company ? $d['company_name'] : null,
            'contact_name' => $company ? $d['contact_name'] : trim($d['first_name'].' '.$d['last_name']),
            'phone' => $d['phone'],
            'city' => $city['name'],
            'address' => $d['billing_address'],
            'doc_type' => $company ? DocumentType::NIT : DocumentType::from($d['doc_type']),
            'doc_dv' => $company ? $d['nit_dv'] : null,
            'first_name' => $company ? null : $d['first_name'],
            'last_name' => $company ? null : $d['last_name'],
            'fiscal_responsibility' => $company ? $d['fiscal_responsibility'] : FiscalResponsibility::NotVatResponsible->value,
            'billing_email' => $d['billing_email'],
            'department_code' => $department['code'],
            'department' => $department['name'],
            'city_code' => $city['code'],
        ];

        if (! $this->customers->nitTakenByAnother($nit, $customer->id)) {
            $changes['nit'] = $nit;
        }

        $this->customers->update($customer, $changes);
    }

    /** @param  Collection<int, Order>  $orders */
    private function notifyGuest(Collection $orders): void
    {
        try {
            Mail::to($orders->first()->billing_email)->send(new GuestOrderReceived($orders));
        } catch (\Throwable $e) {
            // La compra ya quedó guardada: si el correo falla solo se registra (el comercial la ve en el panel)
            Log::error('No se pudo enviar el correo del pedido de invitado '.$orders->pluck('code')->implode(', ').': '.$e->getMessage());
        }
    }
}
