<?php

namespace Tests\Feature\Api\V1;

use App\Enums\CustomerType;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Laura Gómez',
            'email' => 'laura@constructoraaltos.test',
            'password' => 'ClaveSegura2026',
            'company_name' => 'Constructora Altos S.A.S.',
            'nit' => '900765432-8',
            'phone' => '315 555 0202',
            'city' => 'Girón',
            'privacy_accepted' => true,
        ], $overrides);
    }

    public function test_register_creates_a_client_with_company_and_returns_a_working_token(): void
    {
        $response = $this->postJson('/api/v1/register', $this->registration(['marketing_opt_in' => true]))
            ->assertCreated()
            ->assertJsonPath('data.role', 'cliente')
            ->assertJsonPath('data.customer.nit', '900765432-8')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonMissingPath('data.password');

        $user = User::where('email', 'laura@constructoraaltos.test')->firstOrFail();
        $this->assertSame(UserRole::Cliente, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertSame('Laura Gómez', $user->customer->contact_name);
        $this->assertNotNull($user->customer->privacy_accepted_at);
        $this->assertTrue($user->customer->marketing_opt_in);
        $this->assertNotNull($user->customer->marketing_opt_in_at);

        $this->withToken($response->json('token'))->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.email', 'laura@constructoraaltos.test');
    }

    public function test_register_without_type_defaults_to_company(): void
    {
        $this->postJson('/api/v1/register', $this->registration())
            ->assertCreated()
            ->assertJsonPath('data.customer.type', 'company')
            ->assertJsonPath('data.customer.type_label', 'Empresa');

        $this->assertSame(CustomerType::Company, Customer::firstOrFail()->type);
    }

    public function test_register_company_requires_company_name_and_nit(): void
    {
        $this->postJson('/api/v1/register', $this->registration(['type' => 'company', 'company_name' => '', 'nit' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['company_name', 'nit']);

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_register_rejects_an_unknown_type(): void
    {
        $this->postJson('/api/v1/register', $this->registration(['type' => 'gobierno']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('type');
    }

    public function test_register_natural_person_needs_only_the_name(): void
    {
        $response = $this->postJson('/api/v1/register', $this->registration([
            'type' => 'natural',
            'name' => 'Sergio Pardo',
            'email' => 'sergio@correo.test',
            'company_name' => null,
            'nit' => null,
        ]))
            ->assertCreated()
            ->assertJsonPath('data.customer.type', 'natural')
            ->assertJsonPath('data.customer.type_label', 'Persona natural')
            ->assertJsonPath('data.customer.company_name', null)
            ->assertJsonPath('data.customer.nit', null)
            ->assertJsonPath('data.customer.contact_name', 'Sergio Pardo');

        $this->withToken($response->json('token'))->getJson('/api/v1/me')->assertOk();
    }

    public function test_register_natural_person_can_give_an_id_number_and_ignores_company_name(): void
    {
        $this->postJson('/api/v1/register', $this->registration(['type' => 'natural', 'nit' => '1098765432', 'company_name' => 'No aplica S.A.S.']))
            ->assertCreated()
            ->assertJsonPath('data.customer.nit', '1098765432')
            ->assertJsonPath('data.customer.company_name', null);
    }

    public function test_register_natural_person_still_requires_the_name_and_contact_data(): void
    {
        $this->postJson('/api/v1/register', ['type' => 'natural'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'phone', 'city'])
            ->assertJsonMissingValidationErrors(['company_name', 'nit']);
    }

    public function test_two_natural_people_without_id_number_can_register(): void
    {
        $this->postJson('/api/v1/register', $this->registration(['type' => 'natural', 'nit' => null]))->assertCreated();
        $this->postJson('/api/v1/register', $this->registration(['type' => 'natural', 'nit' => null, 'email' => 'otra@correo.test']))->assertCreated();

        $this->assertSame(2, Customer::whereNull('nit')->count());
    }

    public function test_an_id_number_cannot_be_used_twice(): void
    {
        $this->postJson('/api/v1/register', $this->registration(['type' => 'natural', 'nit' => '1098765432']))->assertCreated();

        $this->postJson('/api/v1/register', $this->registration(['type' => 'natural', 'nit' => '1098765432', 'email' => 'otra@correo.test']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nit');
    }

    public function test_register_without_marketing_leaves_it_off(): void
    {
        $this->postJson('/api/v1/register', $this->registration())->assertCreated();

        $customer = Customer::firstOrFail();
        $this->assertFalse($customer->marketing_opt_in);
        $this->assertNull($customer->marketing_opt_in_at);
    }

    public function test_register_ignores_a_role_sent_by_the_client(): void
    {
        $this->postJson('/api/v1/register', $this->registration(['role' => 'admin', 'is_active' => false]))
            ->assertCreated()
            ->assertJsonPath('data.role', 'cliente');

        $this->assertTrue(User::firstOrFail()->is_active);
    }

    public function test_register_requires_accepting_the_privacy_policy(): void
    {
        $this->postJson('/api/v1/register', $this->registration(['privacy_accepted' => false]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('privacy_accepted');

        $this->postJson('/api/v1/register', array_diff_key($this->registration(), ['privacy_accepted' => 1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('privacy_accepted');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_register_validates_required_fields_and_uniqueness(): void
    {
        $this->postJson('/api/v1/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password', 'company_name', 'nit', 'phone', 'city']);

        $this->postJson('/api/v1/register', $this->registration())->assertCreated();

        $this->postJson('/api/v1/register', $this->registration(['email' => 'LAURA@constructoraaltos.test', 'nit' => '111']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email')
            ->assertJsonPath('errors.email.0', 'Ya existe una cuenta con ese correo.');

        $this->postJson('/api/v1/register', $this->registration(['email' => 'otra@empresa.test']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nit');

        $this->postJson('/api/v1/register', $this->registration(['email' => 'otra@empresa.test', 'nit' => '222', 'password' => 'corta']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_login_returns_a_token(): void
    {
        $customer = Customer::factory()->create();

        $this->postJson('/api/v1/login', ['email' => $customer->user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('data.id', $customer->user_id)
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['token']);

        $this->assertSame(1, PersonalAccessToken::count());
    }

    public function test_login_with_wrong_credentials_returns_401(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'El correo o la contraseña no son correctos.']);

        $this->postJson('/api/v1/login', ['email' => 'nadie@empresa.test', 'password' => 'password'])->assertUnauthorized();
    }

    public function test_login_of_an_inactive_user_returns_403(): void
    {
        $user = User::factory()->inactive()->create();

        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])
            ->assertForbidden()
            ->assertJsonPath('message', 'Tu usuario está inactivo. Comunícate con Imagen Eléctrica.');
    }

    public function test_login_validates_the_body(): void
    {
        $this->postJson('/api/v1/login', [])->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_is_rate_limited(): void
    {
        RateLimiter::clear('api-login');
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'mala'])->assertUnauthorized();
        }

        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'mala'])
            ->assertStatus(429)
            ->assertJsonPath('message', 'Demasiados intentos. Espera un momento antes de intentar de nuevo.')
            ->assertHeader('Retry-After');

        // Ni siquiera la clave correcta pasa mientras dure el bloqueo
        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'password'])->assertStatus(429);
    }

    public function test_register_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/register', [])->assertUnprocessable();
        }

        $this->postJson('/api/v1/register', [])->assertStatus(429);
    }

    public function test_logout_revokes_only_the_token_used(): void
    {
        $user = User::factory()->create();
        $first = $user->createToken('api')->plainTextToken;
        $second = $user->createToken('api')->plainTextToken;

        $this->withToken($first)->postJson('/api/v1/logout')->assertNoContent();
        $this->assertSame(1, $user->tokens()->count());

        // Se limpia el guard para que la siguiente petición lea el token otra vez
        $this->app['auth']->forgetGuards();
        $this->withToken($first)->getJson('/api/v1/me')->assertUnauthorized();

        $this->app['auth']->forgetGuards();
        $this->withToken($second)->getJson('/api/v1/me')->assertOk();
    }

    public function test_protected_routes_without_token_return_401_in_spanish(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized()->assertExactJson(['message' => 'No has iniciado sesión o tu token no es válido.']);
        $this->postJson('/api/v1/logout')->assertUnauthorized();
        $this->withToken('token-falso')->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_an_inactive_user_with_a_valid_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;
        $user->forceFill(['is_active' => false])->save();

        $this->withToken($token)->getJson('/api/v1/me')->assertForbidden();
    }

    public function test_me_shows_the_profile_and_company(): void
    {
        $customer = Customer::factory()->create();
        $token = $customer->user->createToken('api')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.email', $customer->user->email)
            ->assertJsonPath('data.customer.company_name', $customer->company_name);
    }

    public function test_me_update_changes_contact_and_company_but_not_nit_or_email(): void
    {
        $customer = Customer::factory()->create(['nit' => '900111222-3']);
        $email = $customer->user->email;
        $token = $customer->user->createToken('api')->plainTextToken;

        $this->withToken($token)->putJson('/api/v1/me', [
            'name' => 'Nuevo Contacto',
            'company_name' => 'Nueva Razón S.A.S.',
            'phone' => '300 000 0000',
            'city' => 'Piedecuesta',
            'address' => 'Calle 1 # 2-3',
            'nit' => '999',
            'email' => 'otro@correo.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Nuevo Contacto')
            ->assertJsonPath('data.customer.company_name', 'Nueva Razón S.A.S.')
            ->assertJsonPath('data.customer.contact_name', 'Nuevo Contacto')
            ->assertJsonPath('data.customer.nit', '900111222-3');

        $this->assertSame($email, $customer->user->fresh()->email);
        $this->assertSame('Piedecuesta', $customer->fresh()->city);
    }

    public function test_natural_person_updates_the_profile_without_company_name(): void
    {
        $customer = Customer::factory()->natural(false)->create();
        $token = $customer->user->createToken('api')->plainTextToken;

        $this->withToken($token)->putJson('/api/v1/me', [
            'name' => 'Nuevo Nombre',
            'company_name' => 'Intento de razón social',
            'phone' => '300 000 0000',
            'city' => 'Piedecuesta',
        ])
            ->assertOk()
            ->assertJsonPath('data.customer.type', 'natural')
            ->assertJsonPath('data.customer.contact_name', 'Nuevo Nombre')
            ->assertJsonPath('data.customer.company_name', null);

        $this->assertNull($customer->fresh()->company_name);
        $this->assertSame(CustomerType::Natural, $customer->fresh()->type);
    }

    public function test_me_update_tracks_marketing_consent_date(): void
    {
        $customer = Customer::factory()->create();
        $payload = ['name' => 'A', 'company_name' => 'B', 'phone' => '1', 'city' => 'C'];
        $token = $customer->user->createToken('api')->plainTextToken;

        $this->withToken($token)->putJson('/api/v1/me', $payload + ['marketing_opt_in' => true])->assertOk();
        $this->assertNotNull($customer->fresh()->marketing_opt_in_at);

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->putJson('/api/v1/me', $payload + ['marketing_opt_in' => false])->assertOk();
        $this->assertNull($customer->fresh()->marketing_opt_in_at);
        $this->assertFalse($customer->fresh()->marketing_opt_in);
    }

    public function test_me_update_validates_required_fields(): void
    {
        $customer = Customer::factory()->create();

        $this->withToken($customer->user->createToken('api')->plainTextToken)
            ->putJson('/api/v1/me', ['name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'company_name', 'phone', 'city']);
    }

    public function test_staff_cannot_update_a_client_profile(): void
    {
        $token = User::factory()->admin()->create()->createToken('api')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.role', 'admin')->assertJsonPath('data.customer', null);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->putJson('/api/v1/me', [])->assertForbidden();
    }
}
