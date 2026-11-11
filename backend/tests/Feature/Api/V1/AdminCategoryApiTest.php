<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** CRUD de categorías por API: solo administrador, con los códigos 200, 201, 204, 401, 403, 404 y 422. */
class AdminCategoryApiTest extends TestCase
{
    use RefreshDatabase;

    private function actAsAdmin(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
    }

    public function test_requires_a_token_and_the_admin_role(): void
    {
        $category = Category::factory()->create();
        $payload = ['name' => 'Nueva'];

        // Sin token: 401
        $this->getJson('/api/v1/admin/categories')->assertUnauthorized();
        $this->postJson('/api/v1/admin/categories', $payload)->assertUnauthorized();
        $this->putJson("/api/v1/admin/categories/{$category->id}", $payload)->assertUnauthorized();
        $this->deleteJson("/api/v1/admin/categories/{$category->id}")->assertUnauthorized();

        // Con token de cliente o comercial: 403
        foreach ([User::factory()->create(), User::factory()->comercial()->create()] as $user) {
            Sanctum::actingAs($user);
            $this->getJson('/api/v1/admin/categories')->assertForbidden();
            $this->getJson("/api/v1/admin/categories/{$category->id}")->assertForbidden();
            $this->postJson('/api/v1/admin/categories', $payload)->assertForbidden();
            $this->putJson("/api/v1/admin/categories/{$category->id}", $payload)->assertForbidden();
            $this->deleteJson("/api/v1/admin/categories/{$category->id}")->assertForbidden();
        }

        $this->assertDatabaseCount('categories', 1);
    }

    public function test_index_includes_inactive_categories_and_searches(): void
    {
        $this->actAsAdmin();
        Category::factory()->create(['name' => 'Motores', 'slug' => 'motores']);
        Category::factory()->create(['name' => 'Cables', 'slug' => 'cables', 'is_active' => false]);

        $this->getJson('/api/v1/admin/categories')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'is_active', 'sort_order']], 'meta']);
        $this->getJson('/api/v1/admin/categories?q=cabl')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Cables');
        $this->getJson('/api/v1/admin/categories?per_page=1')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('meta.per_page', 1);
        $this->getJson('/api/v1/admin/categories?per_page=500')->assertUnprocessable();
    }

    public function test_store_creates_a_category_and_returns_201(): void
    {
        $this->actAsAdmin();

        $this->postJson('/api/v1/admin/categories', ['name' => 'Motores Eléctricos', 'description' => 'Trifásicos'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Motores Eléctricos')
            ->assertJsonPath('data.slug', 'motores-electricos')
            ->assertJsonPath('data.is_active', true)
            ->assertJsonPath('data.sort_order', 0);

        $this->assertDatabaseHas('categories', ['slug' => 'motores-electricos', 'is_active' => true]);
    }

    public function test_store_generates_a_unique_slug_for_repeated_names(): void
    {
        $this->actAsAdmin();

        $this->postJson('/api/v1/admin/categories', ['name' => 'Cables'])->assertCreated()->assertJsonPath('data.slug', 'cables');
        $this->postJson('/api/v1/admin/categories', ['name' => 'Cables'])->assertCreated()->assertJsonPath('data.slug', 'cables-2');
    }

    public function test_store_validates_the_data_with_422(): void
    {
        $this->actAsAdmin();
        $sub = Category::factory()->childOf()->create();

        $this->postJson('/api/v1/admin/categories', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name')
            ->assertJsonStructure(['message', 'errors' => ['name']]);

        // El padre debe existir y ser de primer nivel (solo dos niveles)
        $this->postJson('/api/v1/admin/categories', ['name' => 'X', 'parent_id' => 9999])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->postJson('/api/v1/admin/categories', ['name' => 'X', 'parent_id' => $sub->id])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->postJson('/api/v1/admin/categories', ['name' => 'X', 'sort_order' => -1])->assertUnprocessable()->assertJsonValidationErrors('sort_order');

        $this->assertDatabaseMissing('categories', ['name' => 'X']);
    }

    public function test_show_returns_the_category_or_404(): void
    {
        $this->actAsAdmin();
        $category = Category::factory()->create(['name' => 'Iluminación', 'is_active' => false]);

        $this->getJson("/api/v1/admin/categories/{$category->id}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Iluminación')
            ->assertJsonPath('data.is_active', false);

        $this->getJson('/api/v1/admin/categories/9999')->assertNotFound()->assertJsonStructure(['message']);
    }

    public function test_update_replaces_the_data_and_keeps_the_slug(): void
    {
        $this->actAsAdmin();
        $category = Category::factory()->create(['name' => 'Motores', 'slug' => 'motores', 'is_active' => false]);

        $this->putJson("/api/v1/admin/categories/{$category->id}", ['name' => 'Motores industriales', 'sort_order' => 5])
            ->assertOk()
            ->assertJsonPath('data.name', 'Motores industriales')
            ->assertJsonPath('data.slug', 'motores')
            ->assertJsonPath('data.sort_order', 5)
            // Sin is_active en el JSON se conserva el valor anterior
            ->assertJsonPath('data.is_active', false);
    }

    public function test_update_validates_and_returns_404(): void
    {
        $this->actAsAdmin();
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        $this->putJson("/api/v1/admin/categories/{$parent->id}", ['name' => ''])->assertUnprocessable()->assertJsonValidationErrors('name');
        // No puede ser su propio padre, ni tener padre si ya tiene subcategorías
        $this->putJson("/api/v1/admin/categories/{$child->id}", ['name' => 'Hija', 'parent_id' => $child->id])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $other = Category::factory()->create();
        $this->putJson("/api/v1/admin/categories/{$parent->id}", ['name' => 'Padre', 'parent_id' => $other->id])->assertUnprocessable()->assertJsonValidationErrors('parent_id');

        $this->putJson('/api/v1/admin/categories/9999', ['name' => 'X'])->assertNotFound();
    }

    public function test_destroy_deletes_an_empty_category_with_204(): void
    {
        $this->actAsAdmin();
        $category = Category::factory()->create();

        $this->deleteJson("/api/v1/admin/categories/{$category->id}")->assertNoContent();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->deleteJson("/api/v1/admin/categories/{$category->id}")->assertNotFound();
    }

    public function test_destroy_refuses_a_category_with_products_or_subcategories_with_422(): void
    {
        $this->actAsAdmin();
        $withProducts = Category::factory()->create();
        Product::factory()->create(['category_id' => $withProducts->id]);
        $parent = Category::factory()->create();
        Category::factory()->childOf($parent)->create();

        $this->deleteJson("/api/v1/admin/categories/{$withProducts->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'producto'));
        $this->deleteJson("/api/v1/admin/categories/{$parent->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', fn (string $m) => str_contains($m, 'subcategoría'));

        $this->assertDatabaseHas('categories', ['id' => $withProducts->id]);
        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }
}
