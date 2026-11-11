<?php

namespace Tests\Feature\Api\V1;

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

/** Compra sin cuenta con datos de facturación electrónica y entrega desglosada (POST /api/v1/checkout). */
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

    private function natural(array $override = []): array
    {
        return array_merge([
            'items' => [['product_id' => $this->product->id, 'quantity' => 2]],
            'person_type' => 'natural',
            'doc_type' => 'CC',
            'doc_number' => '1098765432',
            'first_name' => 'Laura',
            'last_name' => 'Gómez Pérez',
            'billing_email' => 'Laura@Correo.test',
            'phone' => '315 555 0202',
            'billing_address' => 'Carrera 27 # 45-10',
            'department_code' => '68',
            'city_code' => '68001',
            'delivery_method' => 'pickup',
            'privacy_accepted' => true,
        ], $override);
    }

    private function company(array $override = []): array
    {
        return $this->natural(array_merge([
            'person_type' => 'company',
            'doc_type' => null,
            'first_name' => null,
            'last_name' => null,
            'doc_number' => '890903938',
            'nit_dv' => '8',
            'company_name' => 'Constructora Altos S.A.S.',
            'fiscal_responsibility' => 'vat_responsible',
            'contact_name' => 'Pedro Ruiz',
        ], $override));
    }

    public function test_a_guest_natural_person_buys_pickup_without_an_account(): void
    {
        $response = $this->postJson('/api/v1/checkout', $this->natural())->assertCreated();

        $response->assertJsonPath('data.0.type', 'purchase')
            ->assertJsonPath('data.0.is_guest', true)
            ->assertJsonPath('meta.guest', true)
            ->assertJsonStructure(['meta' => ['status_urls']])
            ->assertJsonPath('data.0.billing.doc_type', 'CC')
            ->assertJsonPath('data.0.billing.fiscal_responsibility', 'not_vat_responsible')
            ->assertJsonPath('data.0.billing.city_code', '68001')
            ->assertJsonPath('data.0.delivery', null);

        $order = Order::firstOrFail();
        $this->assertNull($order->customer_id);
        $this->assertTrue($order->is_guest);
        $this->assertSame('Laura Gómez Pérez', $order->contact_name);
        $this->assertSame('laura@correo.test', $order->billing_email, 'el correo se guarda en minúscula');
        $this->assertSame('3155550202', $order->phone, 'el celular se guarda sin espacios');
        $this->assertSame('Bucaramanga', $order->city);
        $this->assertSame('Santander', $order->billing_department);
        $this->assertNotNull($order->privacy_accepted_at);
        $this->assertSame(config('shop.privacy_policy_version'), $order->privacy_version);
        $this->assertSame(OrderType::Purchase, $order->type);
        $this->assertSame('238000.00', $order->total, 'precio con IVA × cantidad, recoger en tienda sin envío');

        Mail::assertSent(GuestOrderReceived::class, fn (GuestOrderReceived $m) => $m->hasTo('laura@correo.test'));
    }

    public function test_a_company_with_a_valid_nit_buys_with_home_delivery_in_separate_columns(): void
    {
        $this->postJson('/api/v1/checkout', $this->company([
            'delivery_method' => 'local',
            'delivery_department_code' => '68',
            'delivery_city_code' => '68276',
            'delivery_neighborhood' => 'Zona Industrial',
            'delivery_road' => 'Carrera 9',
            'delivery_number' => '# 12-30',
            'delivery_complement' => 'Bodega 3',
            'delivery_postal_code' => '681004',
            'delivery_notes' => 'Entregar en portería',
        ]))->assertCreated()
            ->assertJsonPath('data.0.billing.doc_type', 'NIT')
            ->assertJsonPath('data.0.billing.doc_dv', '8')
            ->assertJsonPath('data.0.delivery.city', 'Floridablanca')
            ->assertJsonPath('data.0.delivery.neighborhood', 'Zona Industrial');

        $order = Order::firstOrFail();
        $this->assertSame('Constructora Altos S.A.S.', $order->company_name);
        $this->assertSame('890903938-8', $order->nit);
        $this->assertSame('Pedro Ruiz', $order->contact_name);
        $this->assertSame('68276', $order->delivery_city_code);
        $this->assertSame('Carrera 9 # 12-30, Bodega 3 · Barrio Zona Industrial · Floridablanca, Santander', $order->address);
    }

    public function test_delivery_same_as_billing_copies_the_billing_place_and_pickup_has_no_address(): void
    {
        $this->postJson('/api/v1/checkout', $this->natural(['delivery_method' => 'national', 'delivery_same_as_billing' => true]))->assertCreated();
        $order = Order::latest('id')->firstOrFail();

        $this->assertTrue($order->delivery_same_as_billing);
        $this->assertSame('68001', $order->delivery_city_code);
        $this->assertSame('Carrera 27 # 45-10', $order->delivery_road);

        $this->postJson('/api/v1/checkout', $this->natural(['delivery_department_code' => '05', 'delivery_road' => 'ignorada']))->assertCreated();
        $pickup = Order::latest('id')->firstOrFail();
        $this->assertNull($pickup->address);
        $this->assertNull($pickup->delivery_city_code);
    }

    public function test_an_invalid_nit_check_digit_is_rejected_with_the_right_one(): void
    {
        $this->postJson('/api/v1/checkout', $this->company(['nit_dv' => '3']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nit_dv');

        $this->assertSame(0, Order::count());
        $this->assertSame(8, Nit::checkDigit('890903938'));
    }

    public function test_a_nit_written_whole_with_dots_and_dash_is_split_and_validated(): void
    {
        $this->postJson('/api/v1/checkout', $this->company(['doc_number' => '890.903.938-8', 'nit_dv' => null]))->assertCreated();
        $this->assertSame('890903938', Order::firstOrFail()->billing_doc_number);
    }

    public function test_missing_data_returns_422_with_each_field(): void
    {
        $this->postJson('/api/v1/checkout', [
            'items' => [['product_id' => $this->product->id, 'quantity' => 1]],
            'person_type' => 'natural',
            'delivery_method' => 'local',
        ])->assertUnprocessable()->assertJsonValidationErrors([
            'doc_type', 'doc_number', 'first_name', 'last_name', 'billing_email', 'phone', 'billing_address', 'department_code', 'city_code',
            'delivery_department_code', 'delivery_city_code', 'delivery_neighborhood', 'delivery_road', 'delivery_number', 'privacy_accepted',
        ]);
    }

    public function test_a_company_must_give_its_fiscal_data_and_the_city_must_belong_to_the_department(): void
    {
        $this->postJson('/api/v1/checkout', $this->company(['company_name' => null, 'fiscal_responsibility' => null, 'contact_name' => null, 'nit_dv' => null]))
            ->assertUnprocessable()->assertJsonValidationErrors(['company_name', 'fiscal_responsibility', 'contact_name', 'nit_dv']);

        $this->postJson('/api/v1/checkout', $this->natural(['department_code' => '05', 'city_code' => '68001']))
            ->assertUnprocessable()->assertJsonValidationErrors('city_code');

        $this->postJson('/api/v1/checkout', $this->natural(['phone' => '6076000000']))->assertUnprocessable()->assertJsonValidationErrors('phone');
    }

    public function test_the_honeypot_answers_like_a_success_but_saves_nothing(): void
    {
        $this->postJson('/api/v1/checkout', $this->natural(['website' => 'http://spam.test']))->assertCreated()->assertJsonPath('data', []);

        $this->assertSame(0, Order::count());
        Mail::assertNothingSent();
    }

    public function test_the_checkout_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/v1/checkout', ['person_type' => 'natural'])->assertUnprocessable();
        }

        $this->postJson('/api/v1/checkout', ['person_type' => 'natural'])->assertStatus(429);
    }

    public function test_a_logged_in_customer_buys_and_the_billing_data_is_saved_to_the_profile(): void
    {
        $customer = Customer::factory()->create(['nit' => null]);
        $token = $customer->user->createToken('test')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/checkout', $this->natural())->assertCreated()
            ->assertJsonPath('meta.guest', false)
            ->assertJsonPath('data.0.is_guest', false);

        $this->assertSame($customer->id, Order::firstOrFail()->customer_id);
        $profile = $customer->fresh();
        $this->assertSame('1098765432', $profile->nit);
        $this->assertSame('68', $profile->department_code);
        $this->assertSame('68001', $profile->city_code);
        $this->assertSame('Carrera 27 # 45-10', $profile->address);
        Mail::assertNothingSent();

        $me = $this->withToken($token)->getJson('/api/v1/me')->assertOk();
        $me->assertJsonPath('data.customer.doc_type', 'CC')->assertJsonPath('data.customer.city_code', '68001');
    }

    public function test_the_response_tells_when_the_email_already_has_an_account(): void
    {
        User::factory()->create(['email' => 'laura@correo.test']);

        $this->postJson('/api/v1/checkout', $this->natural())->assertCreated()->assertJsonPath('meta.account_exists', true);
    }

    public function test_the_signed_link_shows_the_order_and_a_wrong_or_expired_one_gives_403(): void
    {
        $this->postJson('/api/v1/checkout', $this->natural())->assertCreated();
        $code = Order::firstOrFail()->code;
        $link = GuestOrderLink::make($code);

        $this->getJson("/api/v1/guest/orders/{$code}?".http_build_query($link))->assertOk()->assertJsonPath('data.code', $code);

        $this->getJson("/api/v1/guest/orders/{$code}")->assertForbidden();
        $this->getJson("/api/v1/guest/orders/{$code}?expires={$link['expires']}&signature=falsa")->assertForbidden();
        // La firma de un pedido no abre otro
        $other = 'PED-9999';
        $this->getJson("/api/v1/guest/orders/{$other}?".http_build_query($link))->assertForbidden();

        $this->travel(61)->days();
        $this->getJson("/api/v1/guest/orders/{$code}?".http_build_query($link))->assertForbidden();
    }

    public function test_the_email_carries_the_storefront_link_that_opens_the_order(): void
    {
        $this->postJson('/api/v1/checkout', $this->natural())->assertCreated();

        Mail::assertSent(GuestOrderReceived::class, function (GuestOrderReceived $mail) {
            $html = $mail->render();
            $this->assertStringContainsString(config('shop.frontend_url').'/pedido/PED-', $html);
            $this->assertStringContainsString('signature=', $html);

            return true;
        });
    }

    public function test_one_click_account_uses_the_purchase_data_and_links_the_orders(): void
    {
        $this->postJson('/api/v1/checkout', $this->company())->assertCreated();
        $order = Order::firstOrFail();
        $link = GuestOrderLink::make($order->code);

        $this->postJson("/api/v1/guest/orders/{$order->code}/account", $link + ['password' => 'corta'])->assertUnprocessable();

        $response = $this->postJson("/api/v1/guest/orders/{$order->code}/account", $link + ['password' => 'ClaveSegura2026'])->assertCreated();
        $response->assertJsonPath('data.email', 'laura@correo.test')
            ->assertJsonPath('data.customer.company_name', 'Constructora Altos S.A.S.')
            ->assertJsonPath('data.customer.doc_type', 'NIT')
            ->assertJsonStructure(['token']);

        $this->assertNotNull($order->fresh()->customer_id);

        // Segunda vez: ya tiene cuenta → 422 (la tienda ofrece iniciar sesión)
        $this->postJson("/api/v1/guest/orders/{$order->code}/account", $link + ['password' => 'ClaveSegura2026'])
            ->assertUnprocessable()->assertJsonPath('message', 'Ya existe una cuenta con ese correo. Inicia sesión para ver tus pedidos.');
    }

    public function test_the_account_is_refused_with_a_wrong_signature(): void
    {
        $this->postJson('/api/v1/checkout', $this->natural())->assertCreated();
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

    public function test_the_panel_tags_the_guest_and_shows_the_billing_and_delivery_data(): void
    {
        $this->postJson('/api/v1/checkout', $this->company([
            'delivery_method' => 'local', 'delivery_department_code' => '68', 'delivery_city_code' => '68276',
            'delivery_neighborhood' => 'Zona Industrial', 'delivery_road' => 'Carrera 9', 'delivery_number' => '# 12-30',
        ]))->assertCreated();
        $order = Order::firstOrFail();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk()->assertSee('Invitado')->assertSee('Constructora Altos S.A.S.');

        $this->actingAs($admin)->get(route('admin.orders.show', $order))->assertOk()
            ->assertSee('Invitado')
            ->assertSee('Datos de facturación electrónica')
            ->assertSee('Responsable de IVA')
            ->assertSee('890903938-8')
            ->assertSee('Dirección de entrega')
            ->assertSee('Floridablanca (68276)')
            ->assertSee('Zona Industrial');
    }

    public function test_fields_that_do_not_apply_may_be_absent_like_the_storefront_sends_them(): void
    {
        // La tienda no envía first_name / last_name en una empresa, ni los campos de entrega al recoger
        $payload = $this->company();
        unset($payload['first_name'], $payload['last_name'], $payload['doc_type']);
        $this->postJson('/api/v1/checkout', $payload)->assertCreated()->assertJsonPath('data.0.billing.doc_type', 'NIT');

        $natural = $this->natural(['delivery_method' => 'local', 'delivery_same_as_billing' => true]);
        $this->postJson('/api/v1/checkout', $natural)->assertCreated();
        $this->assertSame(2, Order::count());
    }
}
