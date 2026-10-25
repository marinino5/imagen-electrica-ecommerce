<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_displayed(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Panel');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_login_with_correct_credentials(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_comercial_can_login_with_correct_credentials(): void
    {
        $comercial = User::factory()->comercial()->create();

        $this->post(route('admin.login.store'), ['email' => $comercial->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($comercial);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'incorrecta'])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email' => 'El correo o la contraseña no son correctos.']);

        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post(route('admin.login.store'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_customer_is_denied_access_to_the_panel(): void
    {
        $customer = User::factory()->create(); // rol cliente por defecto

        $this->post(route('admin.login.store'), ['email' => $customer->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Acceso denegado: el panel es solo para el personal de Imagen Eléctrica.']);

        $this->assertGuest();
    }

    public function test_inactive_staff_cannot_login(): void
    {
        $admin = User::factory()->admin()->inactive()->create();

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }
}
