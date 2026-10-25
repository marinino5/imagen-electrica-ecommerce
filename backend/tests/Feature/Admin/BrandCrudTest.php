<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_index_paginates_by_ten_and_searches(): void
    {
        Brand::factory()->count(11)->create();
        Brand::factory()->create(['name' => 'Marca Rarísima']);

        $this->actingAs($this->admin())->get(route('admin.brands.index'))
            ->assertViewHas('brands', fn ($page) => $page->perPage() === 10 && $page->total() === 12);

        $this->actingAs($this->admin())->get(route('admin.brands.index', ['q' => 'rarísima']))
            ->assertSee('Marca Rarísima')
            ->assertViewHas('brands', fn ($page) => $page->total() === 1);
    }

    public function test_store_creates_brand_with_slug(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.brands.store'), ['name' => 'Soler & Palau', 'logo_url' => 'https://example.com/logo.png', 'is_active' => '1'])
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('brands', ['name' => 'Soler & Palau', 'slug' => 'soler-palau', 'is_active' => true]);
    }

    public function test_store_validates_name_unique_and_logo_url(): void
    {
        Brand::factory()->create(['name' => 'WEG']);

        $this->actingAs($this->admin())
            ->post(route('admin.brands.store'), ['name' => 'WEG', 'logo_url' => 'no-es-url'])
            ->assertSessionHasErrors(['name', 'logo_url']);
    }

    public function test_update_keeps_own_name_and_changes_data(): void
    {
        $brand = Brand::factory()->create(['name' => 'WEG']);

        $this->actingAs($this->admin())
            ->put(route('admin.brands.update', $brand), ['name' => 'WEG', 'is_active' => '1'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.brands.index'));
    }

    public function test_brand_with_products_cannot_be_deleted(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->create(['brand_id' => $brand->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.brands.destroy', $brand))
            ->assertSessionHas('error');

        $this->assertModelExists($brand);
    }

    public function test_brand_without_products_can_be_deleted(): void
    {
        $brand = Brand::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.brands.destroy', $brand))
            ->assertSessionHas('status');

        $this->assertModelMissing($brand);
    }

    public function test_comercial_cannot_manage_brands(): void
    {
        $this->actingAs(User::factory()->comercial()->create())
            ->get(route('admin.brands.create'))
            ->assertForbidden();
    }
}
