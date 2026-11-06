<?php

namespace Tests\Feature\Admin;

use App\Enums\QuoteRequestStatus;
use App\Enums\UserRole;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRequestPanelTest extends TestCase
{
    use RefreshDatabase;

    private function quote(array $override = []): QuoteRequest
    {
        return QuoteRequest::create($override + [
            'name' => 'Laura Gómez', 'phone' => '3001234567', 'email' => 'laura@empresa.co', 'city' => 'Bucaramanga',
            'items' => [['product_id' => null, 'sku' => '15331270', 'name' => 'Motor W22 1 HP', 'label' => '1 HP · 1800 RPM', 'line' => 'W22', 'quantity' => 2]],
            'privacy_accepted_at' => now(), 'ip' => '127.0.0.1',
        ]);
    }

    private function staff(UserRole $role): User
    {
        $factory = User::factory();

        return match ($role) {
            UserRole::Admin => $factory->admin()->create(),
            UserRole::Comercial => $factory->comercial()->create(),
            UserRole::Cliente => $factory->create(),
        };
    }

    public function test_admin_y_comercial_ven_el_listado_y_el_detalle(): void
    {
        $quote = $this->quote();

        foreach ([UserRole::Admin, UserRole::Comercial] as $role) {
            $user = $this->staff($role);
            $this->actingAs($user)->get(route('admin.quote-requests.index'))->assertOk()->assertSee('Laura Gómez');
            $this->actingAs($user)->get(route('admin.quote-requests.show', $quote))->assertOk()->assertSee('15331270')->assertSee('laura@empresa.co');
        }
    }

    public function test_cambia_el_estado(): void
    {
        $quote = $this->quote();

        $this->actingAs($this->staff(UserRole::Comercial))
            ->patch(route('admin.quote-requests.update', $quote), ['status' => 'contacted'])
            ->assertRedirect();

        $this->assertSame(QuoteRequestStatus::Contacted, $quote->fresh()->status);
    }

    public function test_el_cliente_y_el_visitante_no_entran(): void
    {
        $this->get(route('admin.quote-requests.index'))->assertRedirect(route('admin.login'));
        $this->actingAs($this->staff(UserRole::Cliente))->get(route('admin.quote-requests.index'))->assertForbidden();
    }
}
