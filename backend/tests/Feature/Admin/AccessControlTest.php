<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, array{string}> */
    public static function catalogRoutes(): array
    {
        return [
            'categorías' => ['admin.categories.index'],
            'marcas' => ['admin.brands.index'],
            'productos' => ['admin.products.index'],
        ];
    }

    #[DataProvider('catalogRoutes')]
    public function test_comercial_cannot_access_catalog_routes(string $route): void
    {
        $this->actingAs(User::factory()->comercial()->create())
            ->get(route($route))
            ->assertForbidden();
    }

    #[DataProvider('catalogRoutes')]
    public function test_admin_can_access_catalog_routes(string $route): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route($route))
            ->assertOk();
    }

    public function test_dashboard_path_redirects_to_admin_home(): void
    {
        $this->actingAs(User::factory()->comercial()->create())
            ->get('/admin/dashboard')
            ->assertRedirect('/admin');
    }

    public function test_comercial_can_access_dashboard(): void
    {
        $this->actingAs(User::factory()->comercial()->create())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_customer_with_session_cannot_access_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('Acceso denegado');
    }

    public function test_deactivated_staff_loses_access(): void
    {
        $this->actingAs(User::factory()->admin()->inactive()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_unknown_page_shows_branded_404(): void
    {
        $this->get('/admin/no-existe')
            ->assertNotFound()
            ->assertSee('Página no encontrada');
    }
}
