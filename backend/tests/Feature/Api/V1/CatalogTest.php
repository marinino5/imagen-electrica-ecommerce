<?php

namespace Tests\Feature\Api\V1;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_public_and_paginated(): void
    {
        Product::factory()->count(15)->create();

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('meta.total', 15)
            ->assertJsonPath('meta.per_page', 12)
            ->assertJsonStructure(['data' => [['id', 'sku', 'name', 'slug', 'price', 'tax_rate', 'stock', 'in_stock', 'sale_mode', 'category', 'brand']], 'links', 'meta']);

        $this->getJson('/api/v1/products?per_page=5&page=3')->assertOk()->assertJsonCount(5, 'data');
    }

    public function test_inactive_and_deleted_products_are_hidden(): void
    {
        $visible = Product::factory()->create();
        Product::factory()->create(['is_active' => false]);
        Product::factory()->create()->delete();

        $this->getJson('/api/v1/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $visible->id);
    }

    public function test_cost_price_never_leaves_the_api(): void
    {
        $product = Product::factory()->create(['cost_price' => 800000]);

        $this->getJson('/api/v1/products')->assertOk()->assertJsonMissingPath('data.0.cost_price');
        $this->getJson("/api/v1/products/{$product->slug}")->assertOk()->assertJsonMissingPath('data.cost_price');
    }

    public function test_search_matches_name_or_sku(): void
    {
        Product::factory()->create(['name' => 'Contactor tripolar 25A', 'sku' => 'CTR-025']);
        Product::factory()->create(['name' => 'Breaker 2x20A', 'sku' => 'BRK-220']);

        $this->getJson('/api/v1/products?q=contactor')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/products?q=BRK-220')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.sku', 'BRK-220');
        // Los comodines de LIKE se buscan como texto
        $this->getJson('/api/v1/products?q=%25')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_filters_by_category_including_subcategories_brand_and_stock(): void
    {
        $parent = Category::factory()->create(['slug' => 'motores']);
        $child = Category::factory()->create(['slug' => 'motores-monofasicos', 'parent_id' => $parent->id]);
        $other = Category::factory()->create(['slug' => 'cables']);
        $siemens = Brand::factory()->create(['slug' => 'siemens']);

        $inParent = Product::factory()->create(['category_id' => $parent->id, 'brand_id' => $siemens->id, 'stock' => 3]);
        $inChild = Product::factory()->create(['category_id' => $child->id, 'stock' => 0]);
        Product::factory()->create(['category_id' => $other->id]);

        $this->getJson('/api/v1/products?category=motores')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/v1/products?category=motores-monofasicos')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $inChild->id);
        $this->getJson('/api/v1/products?brand=siemens')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $inParent->id);
        $this->getJson('/api/v1/products?category=motores&in_stock=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $inParent->id);
    }

    public function test_filters_by_sale_mode_and_featured(): void
    {
        Product::factory()->create();
        $quote = Product::factory()->quoteOnly()->featured()->create();

        $this->getJson('/api/v1/products?sale_mode=quote')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $quote->id);
        $this->getJson('/api/v1/products?featured=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $quote->id);
    }

    public function test_sorts_by_price_leaving_products_without_price_last(): void
    {
        Product::factory()->create(['name' => 'B', 'price' => 300000]);
        Product::factory()->quoteOnly()->create(['name' => 'A']);
        Product::factory()->create(['name' => 'C', 'price' => 100000]);

        $this->getJson('/api/v1/products?sort=price_asc')->assertOk()->assertJsonPath('data.*.name', ['C', 'B', 'A']);
        $this->getJson('/api/v1/products?sort=price_desc')->assertOk()->assertJsonPath('data.*.name', ['B', 'C', 'A']);
    }

    public function test_invalid_filter_values_return_422(): void
    {
        $this->getJson('/api/v1/products?sort=random')->assertUnprocessable()->assertJsonValidationErrors('sort');
        $this->getJson('/api/v1/products?per_page=500')->assertUnprocessable()->assertJsonValidationErrors('per_page');
    }

    public function test_product_detail_by_slug(): void
    {
        $product = Product::factory()->create(['specs' => ['potencia_hp' => 5]]);

        $this->getJson("/api/v1/products/{$product->slug}")
            ->assertOk()
            ->assertJsonPath('data.slug', $product->slug)
            ->assertJsonPath('data.specs.potencia_hp', 5)
            ->assertJsonPath('data.category.id', $product->category_id);
    }

    public function test_product_detail_returns_404_in_spanish_when_missing_or_inactive(): void
    {
        $inactive = Product::factory()->create(['is_active' => false]);

        $this->getJson("/api/v1/products/{$inactive->slug}")->assertNotFound()->assertExactJson(['message' => 'No se encontró lo que buscas.']);
        $this->getJson('/api/v1/products/no-existe')->assertNotFound();
        $this->getJson('/api/v1/ruta-que-no-existe')->assertNotFound()->assertJsonPath('message', 'No se encontró lo que buscas.');
    }

    public function test_categories_and_brands_list_only_active_with_product_counts(): void
    {
        $category = Category::factory()->create();
        Category::factory()->create(['is_active' => false]);
        $brand = Brand::factory()->create();
        Brand::factory()->create(['is_active' => false]);
        Product::factory()->count(2)->create(['category_id' => $category->id, 'brand_id' => $brand->id]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'is_active' => false]);

        $this->getJson('/api/v1/categories')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.products_count', 2);
        $this->getJson('/api/v1/brands')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.products_count', 2);
        $this->getJson('/api/v1/brands?q=zzzz')->assertOk()->assertJsonCount(0, 'data');
    }
}
