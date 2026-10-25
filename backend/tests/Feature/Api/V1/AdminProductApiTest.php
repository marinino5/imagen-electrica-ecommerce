<?php

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminProductApiTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'category_id' => Category::factory()->create()->id,
            'brand_id' => Brand::factory()->create()->id,
            'sku' => 'MOT-5HP-220',
            'name' => 'Motor trifásico 5 HP',
            'short_description' => 'Motor para uso industrial',
            'unit' => 'unidad',
            'sale_mode' => 'direct',
            'price' => 1500000,
            'cost_price' => 1100000,
            'tax_rate' => 19,
            'stock' => 4,
            'specs' => ['potencia_hp' => 5, 'voltaje_v' => 220],
        ], $overrides);
    }

    private function actAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    public function test_requires_an_admin_token(): void
    {
        $product = Product::factory()->create();

        $this->getJson('/api/v1/admin/products')->assertUnauthorized();
        $this->postJson('/api/v1/admin/products', $this->payload())->assertUnauthorized();

        foreach ([User::factory()->create(), User::factory()->comercial()->create()] as $user) {
            Sanctum::actingAs($user);
            $this->getJson('/api/v1/admin/products')->assertForbidden();
            $this->getJson("/api/v1/admin/products/{$product->id}")->assertForbidden();
            $this->postJson('/api/v1/admin/products', $this->payload())->assertForbidden();
            $this->putJson("/api/v1/admin/products/{$product->id}", $this->payload())->assertForbidden();
            $this->deleteJson("/api/v1/admin/products/{$product->id}")->assertForbidden();
        }

        $this->assertDatabaseCount('products', 1);
        $this->assertNull($product->fresh()->deleted_at);
    }

    public function test_index_includes_inactive_products_and_filters(): void
    {
        $this->actAsAdmin();
        Product::factory()->create(['name' => 'Activo']);
        Product::factory()->create(['name' => 'Inactivo', 'is_active' => false]);
        Product::factory()->create(['name' => 'Borrado'])->delete();

        $this->getJson('/api/v1/admin/products')->assertOk()->assertJsonCount(2, 'data')->assertJsonStructure(['data' => [['is_active', 'deleted_at']], 'meta']);
        $this->getJson('/api/v1/admin/products?status=inactive')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Inactivo');
        $this->getJson('/api/v1/admin/products?status=trashed')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Borrado');
        $this->getJson('/api/v1/admin/products?q=activo&per_page=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.per_page', 1);
    }

    public function test_show_returns_the_product_without_the_cost(): void
    {
        $this->actAsAdmin();
        $product = Product::factory()->create(['cost_price' => 500000]);

        $this->getJson("/api/v1/admin/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonMissingPath('data.cost_price');

        $this->getJson('/api/v1/admin/products/99999')->assertNotFound();
    }

    public function test_store_creates_a_product_with_generated_slug_and_numeric_specs(): void
    {
        $this->actAsAdmin();

        $this->postJson('/api/v1/admin/products', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'motor-trifasico-5-hp')
            ->assertJsonPath('data.specs.potencia_hp', 5)
            ->assertJsonPath('data.is_active', true)
            ->assertJsonMissingPath('data.cost_price');

        $product = Product::where('sku', 'MOT-5HP-220')->firstOrFail();
        $this->assertSame(['potencia_hp' => 5, 'voltaje_v' => 220], $product->specs);
        $this->assertSame('1100000.00', $product->cost_price);
    }

    public function test_store_validates_and_returns_422(): void
    {
        $this->actAsAdmin();
        Product::factory()->create(['sku' => 'YA-EXISTE']);

        $this->postJson('/api/v1/admin/products', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['category_id', 'brand_id', 'sku', 'name', 'short_description', 'unit', 'sale_mode', 'tax_rate', 'stock']);

        $this->postJson('/api/v1/admin/products', $this->payload(['sku' => 'YA-EXISTE']))->assertUnprocessable()->assertJsonValidationErrors('sku');
        $this->postJson('/api/v1/admin/products', $this->payload(['price' => null]))
            ->assertUnprocessable()->assertJsonPath('errors.price.0', 'Un producto de venta directa necesita precio.');
        $this->postJson('/api/v1/admin/products', $this->payload(['unit' => 'litro', 'stock' => -1]))->assertUnprocessable()->assertJsonValidationErrors(['unit', 'stock']);
        $this->postJson('/api/v1/admin/products', $this->payload(['specs' => ['Potencia HP' => 5]]))->assertUnprocessable()->assertJsonValidationErrors('specs.0.key');

        // Solo cotización no necesita precio
        $this->postJson('/api/v1/admin/products', $this->payload(['sale_mode' => 'quote', 'price' => null]))->assertCreated()->assertJsonPath('data.price', null);
    }

    public function test_update_replaces_the_product_and_keeps_slug_and_omitted_fields(): void
    {
        $this->actAsAdmin();
        $product = Product::factory()->featured()->create(['name' => 'Nombre viejo', 'slug' => 'nombre-viejo', 'specs' => ['potencia_hp' => 2], 'is_active' => false]);

        $this->putJson("/api/v1/admin/products/{$product->id}", $this->payload(['name' => 'Nombre nuevo', 'stock' => 9]))
            ->assertOk()
            ->assertJsonPath('data.name', 'Nombre nuevo')
            ->assertJsonPath('data.stock', 9)
            ->assertJsonPath('data.slug', 'nombre-viejo');

        $fresh = $product->fresh();
        $this->assertFalse($fresh->is_active);
        $this->assertTrue($fresh->is_featured);
        $this->assertSame(['potencia_hp' => 5, 'voltaje_v' => 220], $fresh->specs);

        // Sin "specs" en la petición se conservan las que ya tiene
        $body = $this->payload(['sku' => 'OTRO-SKU']);
        unset($body['specs']);
        $this->putJson("/api/v1/admin/products/{$product->id}", $body)->assertOk();
        $this->assertSame(['potencia_hp' => 5, 'voltaje_v' => 220], $product->fresh()->specs);
    }

    public function test_update_allows_keeping_its_own_sku_but_not_taking_anothers(): void
    {
        $this->actAsAdmin();
        $product = Product::factory()->create(['sku' => 'MIO']);
        Product::factory()->create(['sku' => 'AJENO']);

        $this->putJson("/api/v1/admin/products/{$product->id}", $this->payload(['sku' => 'MIO']))->assertOk();
        $this->putJson("/api/v1/admin/products/{$product->id}", $this->payload(['sku' => 'AJENO']))->assertUnprocessable()->assertJsonValidationErrors('sku');
    }

    public function test_destroy_soft_deletes_and_returns_204(): void
    {
        $this->actAsAdmin();
        $product = Product::factory()->create();

        $this->deleteJson("/api/v1/admin/products/{$product->id}")->assertNoContent();

        $this->assertSoftDeleted($product);
        $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
        $this->deleteJson("/api/v1/admin/products/{$product->id}")->assertNotFound();
    }
}
