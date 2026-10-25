<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_search(): void
    {
        $this->get(route('admin.search', ['q' => 'motor']))->assertRedirect(route('admin.login'));
    }

    public function test_customer_cannot_search(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.search', ['q' => 'motor']))
            ->assertForbidden();
    }

    public function test_search_finds_products_by_name_and_by_sku(): void
    {
        Product::factory()->create(['name' => 'Variador de frecuencia', 'sku' => 'VAR-100']);
        Product::factory()->create(['name' => 'Cable THHN 12', 'sku' => 'CAB-200']);

        $this->actingAs(User::factory()->comercial()->create())
            ->get(route('admin.search', ['q' => 'variador']))
            ->assertOk()
            ->assertSee('Variador de frecuencia')
            ->assertDontSee('Cable THHN 12');

        $this->actingAs(User::factory()->comercial()->create())
            ->get(route('admin.search', ['q' => 'CAB-2']))
            ->assertSee('Cable THHN 12')
            ->assertDontSee('Variador de frecuencia');
    }

    public function test_comercial_sees_results_without_links_to_the_product(): void
    {
        $product = Product::factory()->create(['name' => 'Motor sin enlace']);

        $this->actingAs(User::factory()->comercial()->create())
            ->get(route('admin.search', ['q' => 'sin enlace']))
            ->assertSee('Motor sin enlace')
            ->assertDontSee(route('admin.products.show', $product));
    }

    public function test_admin_sees_a_link_to_each_product(): void
    {
        $product = Product::factory()->create(['name' => 'Motor con enlace']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.search', ['q' => 'con enlace']))
            ->assertSee(route('admin.products.show', $product));
    }

    public function test_wildcards_are_searched_as_plain_text(): void
    {
        Product::factory()->create(['name' => 'Motor normal']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.search', ['q' => '%%']))
            ->assertSee('Sin resultados');
    }

    public function test_short_term_asks_for_more_letters(): void
    {
        Product::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.search', ['q' => 'a']))
            ->assertOk()
            ->assertSee('Escribe qué buscas');
    }

    public function test_no_matches_shows_empty_state(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.search', ['q' => 'inexistente']))
            ->assertSee('Sin resultados');
    }
}
