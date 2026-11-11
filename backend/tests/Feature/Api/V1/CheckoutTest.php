<?php

namespace Tests\Feature\Api\V1;

use App\Enums\IdType;
use App\Enums\OrderType;
use App\Mail\GuestOrderReceived;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Support\GuestOrderLink;
use App\Support\Nit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/** La compra por pasos: entrega, datos de contacto y factura electrónica opcional con los campos de la API de clientes de Siigo. */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        RateLimiter::clear('api-checkout');
        $this->product = Product::factory()->create(['price' => 119000, 'stock' => 10]);
    }

    /** Lo mínimo: recoger en tienda, sin factura. */
    private function base(array $override = []): array
    {
        return array_merge([
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'delivery_method' => 'pickup',
            'name' => 'Laura Gómez Pérez',
            'phone' => '315 555 0202',
            'email' => 'Laura@Correo.test',
            'needs_invoice' => false,
            'privacy_accepted' => true,
        ], $override);
    }

    private function delivery(string $state, string $city, array $override = []): array
    {
        return array_merge([
            'delivery_method' => 'delivery', 'delivery_state_code' => $state, 'delivery_city_code' => $city,
            'delivery_neighborhood' => 'Zona Industrial', 'delivery_road' => 'Carrera 9', 'delivery_number' => '# 12-30',
        ], $override);
    }

    private function person(array $override = []): array
    {
        return array_merge([
            'needs_invoice' => true, 'person_type' => 'Person', 'id_type' => '13', 'identification' => '1098765432',
            'first_name' => 'Laura', 'last_name' => 'Gómez Pérez', 'billing_address' => 'Carrera 27 # 45-10',
            'billing_state_code' => '68', 'billing_city_code' => '68001',
        ], $override);
    }

    private function company(array $override = []): array
    {
        return array_merge([
            'needs_invoice' => true, 'person_type' => 'Company', 'identification' => '890903938', 'check_digit' => '8',
            'company_name' => 'Constructora Altos S.A.S.', 'vat_responsible' => true, 'fiscal_responsibilities' => ['O-13'],
            'billing_address' => 'Carrera 27 # 45-10', 'billing_state_code' => '68', 'billing_city_code' => '68001', 'billing_email' => 'Facturas@Altos.test',
        ], $override);
    }

    private function checkout(array $payload)
    {
        return $this->postJson('/api/v1/checkout', $payload);
    }

    public function test_a_guest_buys_pickup_with_only_contact_data_and_no_invoice(): void
    {
        $this->checkout($this->base())->assertCreated()
            ->assertJsonPath('data.0.type', 'purchase')
            ->assertJsonPath('data.0.is_guest', true)
            ->assertJsonPath('data.0.needs_invoice', false)
            ->assertJsonPath('data.0.billing', null)
            ->assertJsonPath('data.0.delivery', null)
            ->assertJsonPath('meta.guest', true)
            ->assertJsonStructure(['meta' => ['status_urls']]);

        $order = Order::firstOrFail();
        $this->assertNull($order->customer_id);
        $this->assertSame('Laura Gómez Pérez', $order->contact_name);
        $this->assertSame('laura@correo.test', $order->email);
        $this->assertSame('3155550202', $order->phone, 'el celular se guarda sin espacios');
        $this->assertNull($order->billing_identification, 'sin factura no se guarda ningún documento');
        $this->assertNull($order->address);
        $this->assertSame('pickup', $order->delivery_method->value);
        $this->assertSame('238000.00', $order->total, 'recoger en tienda: sin envío');
        $this->assertNotNull($order->privacy_accepted_at);
        $this->assertSame(config('shop.privacy_policy_version'), $order->privacy_version);

        Mail::assertSent(GuestOrderReceived::class, fn (GuestOrderReceived $m) => $m->hasTo('laura@correo.test'));
    }

    public function test_an_invoice_to_a_person_stores_the_fields_of_the_siigo_customer_api(): void
    {
        $this->checkout($this->base($this->person()))->assertCreated()
            ->assertJsonPath('data.0.billing.person_type', 'Person')
            ->assertJsonPath('data.0.billing.id_type', '13')
            ->assertJsonPath('data.0.billing.identification', '1098765432')
            ->assertJsonPath('data.0.billing.check_digit', null)
            ->assertJsonPath('data.0.billing.name', ['Laura', 'Gómez Pérez'])
            ->assertJsonPath('data.0.billing.vat_responsible', false)
            ->assertJsonPath('data.0.billing.fiscal_responsibilities', ['R-99-PN'])
            ->assertJsonPath('data.0.billing.address.city.country_code', 'CO')
            ->assertJsonPath('data.0.billing.address.city.state_code', '68')
            ->assertJsonPath('data.0.billing.address.city.city_code', '68001')
            ->assertJsonPath('data.0.billing.contact.first_name', 'Laura');

        $order = Order::firstOrFail();
        $this->assertTrue($order->needs_invoice);
        $this->assertSame(IdType::CC, $order->billing_id_type);
        $this->assertSame('Bucaramanga', $order->city);
        $this->assertSame('Santander', $order->billing_department);
    }

    public function test_a_company_with_a_valid_nit_and_fiscal_data(): void
    {
        $this->checkout($this->base($this->company()))->assertCreated()
            ->assertJsonPath('data.0.billing.person_type', 'Company')
            ->assertJsonPath('data.0.billing.id_type', '31')
            ->assertJsonPath('data.0.billing.check_digit', '8')
            ->assertJsonPath('data.0.billing.name', ['Constructora Altos S.A.S.'])
            ->assertJsonPath('data.0.billing.vat_responsible', true)
            ->assertJsonPath('data.0.billing.fiscal_responsibilities', ['O-13'])
            ->assertJsonPath('data.0.billing.email', 'facturas@altos.test');

        $order = Order::firstOrFail();
        $this->assertSame('890903938-8', $order->nit);
        $this->assertSame('Constructora Altos S.A.S.', $order->company_name);
        $this->assertSame('Laura', $order->billing_contact_first_name, 'el contacto de la empresa sale del nombre de quien compra');
        $this->assertSame('Gómez Pérez', $order->billing_contact_last_name);
    }

    public function test_local_delivery_in_floridablanca_costs_the_local_rate(): void
    {
        $this->checkout($this->base($this->delivery('68', '68276')))->assertCreated()
            ->assertJsonPath('data.0.delivery_method', 'local')
            ->assertJsonPath('data.0.delivery.city', 'Floridablanca')
            ->assertJsonPath('data.0.delivery.state_code', '68')
            ->assertJsonPath('data.0.shipping', 15000);

        $order = Order::firstOrFail();
        $this->assertSame('253000.00', $order->total, '2 × 119.000 + envío local de 15.000');
        $this->assertSame('Carrera 9 # 12-30 · Barrio Zona Industrial · Floridablanca, Santander', $order->address);
    }

    public function test_every_metropolitan_municipality_is_local_and_the_rest_of_the_country_is_national(): void
    {
        foreach (['68001', '68276', '68307', '68547'] as $city) {
            $this->checkout($this->base($this->delivery('68', $city)))->assertCreated()->assertJsonPath('data.0.delivery_method', 'local');
        }

        // Medellín: nacional, tarifa del resto del país
        $this->checkout($this->base($this->delivery('05', '05001')))->assertCreated()
            ->assertJsonPath('data.0.delivery_method', 'national')->assertJsonPath('data.0.shipping', 35000);

        // Barrancabermeja (resto de Santander): nacional con su propia tarifa
        $this->checkout($this->base($this->delivery('68', '68081')))->assertCreated()
            ->assertJsonPath('data.0.delivery_method', 'national')->assertJsonPath('data.0.shipping', 25000);
    }

    public function test_shipping_is_free_over_the_minimum_and_zones_come_from_the_config_table(): void
    {
        config(['shop.shipping.free_shipping_from' => 200000]);
        $this->checkout($this->base($this->delivery('05', '05001')))->assertCreated()->assertJsonPath('data.0.shipping', 0);

        config(['shop.shipping.free_shipping_from' => 9000000]);
        $zones = config('shop.shipping.zones');
        $zones[2]['cost'] = 41000;
        config(['shop.shipping.zones' => $zones]);
        $this->checkout($this->base($this->delivery('05', '05001')))->assertCreated()->assertJsonPath('data.0.shipping', 41000);
    }

    public function test_the_quote_endpoint_gives_the_method_zone_and_cost(): void
    {
        $this->getJson('/api/v1/shipping/quote?city_code=68276&subtotal=1000')->assertOk()
            ->assertJsonPath('data.method', 'local')->assertJsonPath('data.zone', 'local')->assertJsonPath('data.cost', 15000);
        $this->getJson('/api/v1/shipping/quote?city_code=05001')->assertOk()->assertJsonPath('data.method', 'national')->assertJsonPath('data.cost', 35000);
        $this->getJson('/api/v1/shipping/quote?city_code=99999')->assertUnprocessable();
    }

    public function test_billing_same_as_delivery_copies_the_delivery_place(): void
    {
        $this->checkout($this->base($this->delivery('68', '68276') + $this->person(['billing_same_as_delivery' => true, 'billing_address' => null, 'billing_state_code' => null, 'billing_city_code' => null])))
            ->assertCreated();

        $order = Order::firstOrFail();
        $this->assertSame('68276', $order->billing_city_code);
        $this->assertSame('Carrera 9 # 12-30', $order->billing_address);
        $this->assertSame('Floridablanca', $order->city);
    }

    public function test_an_invalid_nit_check_digit_is_rejected_with_the_right_one(): void
    {
        $this->checkout($this->base($this->company(['check_digit' => '3'])))->assertUnprocessable()->assertJsonValidationErrors('check_digit');

        $this->assertSame(0, Order::count());
        $this->assertSame(8, Nit::checkDigit('890903938'));
    }

    public function test_a_nit_written_whole_with_dots_and_dash_is_split_and_validated(): void
    {
        $this->checkout($this->base($this->company(['identification' => '890.903.938-8', 'check_digit' => null])))->assertCreated();
        $this->assertSame('890903938', Order::firstOrFail()->billing_identification);
    }

    public function test_foreign_documents_may_have_letters_but_a_cedula_may_not(): void
    {
        $this->checkout($this->base($this->person(['id_type' => '41', 'identification' => 'ab123456'])))->assertCreated()->assertJsonPath('data.0.billing.identification', 'AB123456');
        $this->checkout($this->base($this->person(['id_type' => '13', 'identification' => 'AB123'])))->assertUnprocessable()->assertJsonValidationErrors('identification');
    }

    public function test_the_steps_validate_only_what_they_ask(): void
    {
        // Entrega a domicilio sin dirección + sin datos de contacto
        $this->checkout(['items' => [['product_id' => $this->product->id, 'quantity' => 1]], 'delivery_method' => 'delivery'])
            ->assertUnprocessable()->assertJsonValidationErrors([
                'delivery_state_code', 'delivery_city_code', 'delivery_neighborhood', 'delivery_road', 'delivery_number', 'name', 'phone', 'email', 'privacy_accepted',
            ]);

        // Con factura: faltan los campos de la factura
        $this->checkout($this->base(['needs_invoice' => true]))->assertUnprocessable()->assertJsonValidationErrors(['person_type', 'identification', 'billing_address', 'billing_city_code']);
        $this->checkout($this->base($this->company(['company_name' => null, 'check_digit' => null])))->assertUnprocessable()->assertJsonValidationErrors(['company_name', 'check_digit']);
        $this->checkout($this->base($this->person(['first_name' => null, 'last_name' => null])))->assertUnprocessable()->assertJsonValidationErrors(['first_name', 'last_name']);
    }

    public function test_the_municipality_must_belong_to_the_department_and_the_phone_must_be_a_colombian_cell(): void
    {
        $this->checkout($this->base($this->delivery('05', '68001')))->assertUnprocessable()->assertJsonValidationErrors('delivery_city_code');
        $this->checkout($this->base($this->person(['billing_state_code' => '05', 'billing_city_code' => '68001'])))->assertUnprocessable()->assertJsonValidationErrors('billing_city_code');
        $this->checkout($this->base(['phone' => '6076000000']))->assertUnprocessable()->assertJsonValidationErrors('phone');
    }

    public function test_fiscal_codes_must_be_the_ones_siigo_accepts(): void
    {
        $this->checkout($this->base($this->company(['fiscal_responsibilities' => ['X-99']])))->assertUnprocessable()->assertJsonValidationErrors('fiscal_responsibilities.0');
        foreach (['R-99-PN', 'O-13', 'O-15', 'O-23', 'O-47'] as $code) {
            $this->checkout($this->base($this->company(['fiscal_responsibilities' => [$code]])))->assertCreated();
        }
    }

    public function test_the_honeypot_answers_like_a_success_but_saves_nothing(): void
    {
        $this->checkout($this->base(['website' => 'http://spam.test']))->assertCreated()->assertJsonPath('data', []);

        $this->assertSame(0, Order::count());
        Mail::assertNothingSent();
    }

    public function test_the_checkout_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->checkout(['delivery_method' => 'pickup'])->assertUnprocessable();
        }

        $this->checkout(['delivery_method' => 'pickup'])->assertStatus(429);
    }

    public function test_a_logged_in_customer_buys_and_contact_invoice_and_address_are_saved_to_the_profile(): void
    {
        $customer = Customer::factory()->create(['nit' => null]);
        $token = $customer->user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/checkout', $this->base($this->delivery('68', '68276') + $this->person()))->assertCreated()
            ->assertJsonPath('meta.guest', false)->assertJsonPath('data.0.is_guest', false);

        $this->assertSame($customer->id, Order::firstOrFail()->customer_id);
        $profile = $customer->fresh();
        $this->assertSame('1098765432', $profile->nit);
        $this->assertSame(IdType::CC, $profile->id_type);
        $this->assertSame('68001', $profile->city_code);
        $this->assertSame('Laura Gómez Pérez', $profile->contact_name);
        $this->assertCount(1, $profile->delivery_addresses);
        $this->assertSame('68276', $profile->delivery_addresses[0]['city_code']);
        Mail::assertNothingSent();

        // Comprar de nuevo con la misma dirección no la duplica
        $this->withToken($token)->postJson('/api/v1/checkout', $this->base($this->delivery('68', '68276')))->assertCreated();
        $this->assertCount(1, $customer->fresh()->delivery_addresses);

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()
            ->assertJsonPath('data.customer.id_type', '13')->assertJsonPath('data.customer.city_code', '68001')->assertJsonPath('data.customer.delivery_addresses.0.city_code', '68276');
    }

    public function test_the_response_tells_when_the_email_already_has_an_account(): void
    {
        User::factory()->create(['email' => 'laura@correo.test']);

        $this->checkout($this->base())->assertCreated()->assertJsonPath('meta.account_exists', true);
    }

    public function test_the_signed_link_shows_the_order_and_a_wrong_or_expired_one_gives_403(): void
    {
        $this->checkout($this->base())->assertCreated();
        $code = Order::firstOrFail()->code;
        $link = GuestOrderLink::make($code);

        $this->getJson("/api/v1/guest/orders/{$code}?".http_build_query($link))->assertOk()->assertJsonPath('data.code', $code);

        $this->getJson("/api/v1/guest/orders/{$code}")->assertForbidden();
        $this->getJson("/api/v1/guest/orders/{$code}?expires={$link['expires']}&signature=falsa")->assertForbidden();
        $this->getJson('/api/v1/guest/orders/PED-9999?'.http_build_query($link))->assertForbidden();

        $this->travel(61)->days();
        $this->getJson("/api/v1/guest/orders/{$code}?".http_build_query($link))->assertForbidden();
    }

    public function test_the_email_carries_the_storefront_link_that_opens_the_order(): void
    {
        $this->checkout($this->base())->assertCreated();

        Mail::assertSent(GuestOrderReceived::class, function (GuestOrderReceived $mail) {
            $html = $mail->render();
            $this->assertStringContainsString(config('shop.frontend_url').'/pedido/PED-', $html);
            $this->assertStringContainsString('signature=', $html);

            return true;
        });
    }

    public function test_one_click_account_uses_the_purchase_data_and_links_the_orders(): void
    {
        $this->checkout($this->base($this->company()))->assertCreated();
        $order = Order::firstOrFail();
        $link = GuestOrderLink::make($order->code);

        $this->postJson("/api/v1/guest/orders/{$order->code}/account", $link + ['password' => 'corta'])->assertUnprocessable();

        $this->postJson("/api/v1/guest/orders/{$order->code}/account", $link + ['password' => 'ClaveSegura2026'])->assertCreated()
            ->assertJsonPath('data.email', 'laura@correo.test')
            ->assertJsonPath('data.customer.company_name', 'Constructora Altos S.A.S.')
            ->assertJsonPath('data.customer.id_type', '31')
            ->assertJsonPath('data.customer.check_digit', '8')
            ->assertJsonStructure(['token']);

        $this->assertNotNull($order->fresh()->customer_id);

        $this->postJson("/api/v1/guest/orders/{$order->code}/account", $link + ['password' => 'ClaveSegura2026'])
            ->assertUnprocessable()->assertJsonPath('message', 'Ya existe una cuenta con ese correo. Inicia sesión para ver tus pedidos.');
    }

    public function test_the_account_is_refused_with_a_wrong_signature(): void
    {
        $this->checkout($this->base())->assertCreated();
        $code = Order::firstOrFail()->code;

        $this->postJson("/api/v1/guest/orders/{$code}/account", ['expires' => time() + 100, 'signature' => 'x', 'password' => 'ClaveSegura2026'])->assertForbidden();
    }

    public function test_locations_list_departments_and_municipalities_with_dane_codes(): void
    {
        $departments = $this->getJson('/api/v1/locations/departments')->assertOk()->json('data');
        $this->assertCount(33, $departments);
        $this->assertContains(['code' => '68', 'name' => 'Santander'], $departments);

        $cities = $this->getJson('/api/v1/locations/departments/68/municipalities')->assertOk()->json('data');
        $this->assertContains(['code' => '68001', 'name' => 'Bucaramanga'], $cities);
        $this->assertTrue(collect($cities)->every(fn ($c) => str_starts_with($c['code'], '68')));

        $this->getJson('/api/v1/locations/departments/99999/municipalities')->assertNotFound();
    }

    public function test_the_panel_tags_the_guest_and_shows_the_invoice_and_delivery_data(): void
    {
        $this->checkout($this->base($this->delivery('68', '68276') + $this->company()))->assertCreated();
        $order = Order::firstOrFail();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk()->assertSee('Invitado')->assertSee('Constructora Altos S.A.S.');

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Invitado')
            ->assertSee('Datos de facturación electrónica')
            ->assertSee('fiscal_responsibilities', false)
            ->assertSee('O-13')
            ->assertSee('890903938-8')
            ->assertSee('Dirección de entrega')
            ->assertSee('Floridablanca (68276)')
            ->assertSee('Zona Industrial');

        // Sin factura el panel lo dice
        $this->checkout($this->base(['email' => 'otra@correo.test']))->assertCreated();
        $this->actingAs($admin)->get(route('admin.orders.show', Order::latest('id')->first()))->assertOk()->assertSee('no pidió factura');
    }

    public function test_the_storefront_copy_of_the_dane_list_is_identical_to_the_backend_one(): void
    {
        $this->assertFileEquals(database_path('data/divipola.json'), base_path('../cliente/src/data/divipola.json'));
    }
}
