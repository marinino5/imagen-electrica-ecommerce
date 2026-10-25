<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
            'sku' => 'REF-001',
            'name' => 'Motor trifásico 5 HP',
            'short_description' => 'Motor para uso industrial',
            'unit' => 'unidad',
            'sale_mode' => 'direct',
            'price' => '1500000',
            'tax_rate' => '19',
            'stock' => '4',
            'is_active' => '1',
            'specs' => [
                ['key' => 'potencia_hp', 'value' => '5'],
                ['key' => 'voltaje_v', 'value' => '220'],
                ['key' => '', 'value' => ''],
            ],
        ], $overrides);
    }

    public function test_index_paginates_by_ten(): void
    {
        Product::factory()->count(12)->create();

        $this->actingAs($this->admin())->get(route('admin.products.index'))
            ->assertOk()
            ->assertViewHas('products', fn ($page) => $page->perPage() === 10 && $page->total() === 12);
    }

    public function test_index_searches_by_name_or_sku(): void
    {
        Product::factory()->create(['name' => 'Variador Delta', 'sku' => 'VFD-1']);
        Product::factory()->create(['name' => 'Motor WEG', 'sku' => 'MOT-1']);

        $this->actingAs($this->admin())->get(route('admin.products.index', ['q' => 'VFD']))
            ->assertViewHas('products', fn ($page) => $page->total() === 1);
    }

    public function test_index_filters_by_category_including_subcategories(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);
        Product::factory()->create(['category_id' => $parent->id]);
        Product::factory()->create(['category_id' => $child->id]);
        Product::factory()->create();

        $this->actingAs($this->admin())->get(route('admin.products.index', ['category_id' => $parent->id]))
            ->assertViewHas('products', fn ($page) => $page->total() === 2);

        $this->actingAs($this->admin())->get(route('admin.products.index', ['category_id' => $child->id]))
            ->assertViewHas('products', fn ($page) => $page->total() === 1);
    }

    public function test_index_filters_by_brand_and_sale_mode(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->create(['brand_id' => $brand->id]);
        Product::factory()->quoteOnly()->create(['brand_id' => $brand->id]);
        Product::factory()->create();

        $this->actingAs($this->admin())->get(route('admin.products.index', ['brand_id' => $brand->id]))
            ->assertViewHas('products', fn ($page) => $page->total() === 2);

        $this->actingAs($this->admin())->get(route('admin.products.index', ['brand_id' => $brand->id, 'sale_mode' => 'quote']))
            ->assertViewHas('products', fn ($page) => $page->total() === 1);
    }

    public function test_index_filters_by_low_and_out_of_stock(): void
    {
        Product::factory()->create(['stock' => 0]);
        Product::factory()->create(['stock' => 3]);
        Product::factory()->create(['stock' => 5]);
        Product::factory()->create(['stock' => 6]);

        $this->actingAs($this->admin())->get(route('admin.products.index', ['stock' => 'low']))
            ->assertViewHas('products', fn ($page) => $page->total() === 3);

        $this->actingAs($this->admin())->get(route('admin.products.index', ['stock' => 'out']))
            ->assertViewHas('products', fn ($page) => $page->total() === 1);
    }

    public function test_index_ignores_unknown_filter_values(): void
    {
        Product::factory()->count(2)->create();

        $this->actingAs($this->admin())->get(route('admin.products.index', ['stock' => 'xx', 'sale_mode' => 'zz', 'status' => 'qq']))
            ->assertOk()
            ->assertViewHas('products', fn ($page) => $page->total() === 2);
    }

    public function test_store_creates_product_with_slug_and_typed_specs(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->payload())
            ->assertRedirect(route('admin.products.index'));

        $product = Product::where('sku', 'REF-001')->firstOrFail();
        $this->assertSame('motor-trifasico-5-hp', $product->slug);
        $this->assertSame(['potencia_hp' => 5, 'voltaje_v' => 220], $product->specs);
    }

    public function test_store_validates_required_fields_and_unique_sku(): void
    {
        Product::factory()->create(['sku' => 'REF-001']);

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->payload(['name' => '', 'stock' => '-1']))
            ->assertSessionHasErrors(['sku', 'name', 'stock']);
    }

    public function test_direct_sale_product_requires_price_but_quote_product_does_not(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->payload(['price' => '']))
            ->assertSessionHasErrors('price');

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->payload(['price' => '', 'sale_mode' => 'quote']))
            ->assertSessionHasNoErrors();

        $this->assertNull(Product::where('sku', 'REF-001')->first()->price);
    }

    public function test_spec_names_must_be_lowercase_snake_case_and_unique(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), $this->payload(['specs' => [
                ['key' => 'Potencia HP', 'value' => '5'],
                ['key' => 'voltaje_v', 'value' => '220'],
                ['key' => 'voltaje_v', 'value' => '440'],
            ]]))
            ->assertSessionHasErrors(['specs.0.key', 'specs.1.key', 'specs.2.key']);
    }

    public function test_update_keeps_own_sku_and_replaces_specs(): void
    {
        $product = Product::factory()->create(['sku' => 'REF-001']);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), $this->payload(['specs' => [['key' => 'corriente_a', 'value' => '25.5']]]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.products.index'));

        $this->assertSame(['corriente_a' => 25.5], $product->fresh()->specs);
    }

    public function test_destroy_is_soft_delete_and_restore_brings_it_back(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertSoftDeleted($product);

        $this->actingAs($this->admin())->get(route('admin.products.index', ['status' => 'trashed']))
            ->assertViewHas('products', fn ($page) => $page->total() === 1);

        $this->actingAs($this->admin())
            ->patch(route('admin.products.restore', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertNotSoftDeleted($product);
    }

    public function test_create_and_edit_forms_render(): void
    {
        $product = Product::factory()->create(['specs' => ['potencia_hp' => 5]]);

        $this->actingAs($this->admin())->get(route('admin.products.create'))->assertOk();
        $this->actingAs($this->admin())->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('potencia_hp');
    }

    public function test_comercial_cannot_manage_products(): void
    {
        $comercial = User::factory()->comercial()->create();

        $this->actingAs($comercial)->get(route('admin.products.create'))->assertForbidden();
        $this->actingAs($comercial)->post(route('admin.products.store'), $this->payload())->assertForbidden();
    }
}
