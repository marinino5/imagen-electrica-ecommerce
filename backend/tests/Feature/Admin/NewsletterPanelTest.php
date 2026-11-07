<?php

namespace Tests\Feature\Admin;

use App\Models\NewsletterSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterPanelTest extends TestCase
{
    use RefreshDatabase;

    private function subscription(string $email): NewsletterSubscription
    {
        return NewsletterSubscription::create(['email' => $email, 'consent' => true, 'consented_at' => now(), 'ip' => '127.0.0.1']);
    }

    public function test_admin_y_comercial_ven_los_suscriptores_y_pueden_buscar(): void
    {
        $this->subscription('laura@empresa.co');
        $this->subscription('pedro@otra.co');

        foreach ([User::factory()->admin()->create(), User::factory()->comercial()->create()] as $user) {
            $this->actingAs($user)->get(route('admin.newsletter.index'))
                ->assertOk()->assertSee('laura@empresa.co')->assertSee('pedro@otra.co');

            $this->actingAs($user)->get(route('admin.newsletter.index', ['q' => 'laura']))
                ->assertOk()->assertSee('laura@empresa.co')->assertDontSee('pedro@otra.co');
        }
    }

    public function test_un_cliente_o_un_visitante_no_entran(): void
    {
        $this->get(route('admin.newsletter.index'))->assertRedirect(route('admin.login'));

        $this->actingAs(User::factory()->create())->get(route('admin.newsletter.index'))->assertForbidden();
    }
}
