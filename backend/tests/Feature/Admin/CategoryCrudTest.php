<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_index_paginates_by_ten_and_searches_by_name(): void
    {
        Category::factory()->count(12)->create();
        Category::factory()->create(['name' => 'Tableros especiales']);

        $this->actingAs($this->admin())->get(route('admin.categories.index'))
            ->assertOk()
            ->assertViewHas('categories', fn ($page) => $page->perPage() === 10 && $page->total() === 13);

        $this->actingAs($this->admin())->get(route('admin.categories.index', ['q' => 'especiales']))
            ->assertOk()
            ->assertSee('Tableros especiales')
            ->assertViewHas('categories', fn ($page) => $page->total() === 1);
    }

    public function test_store_creates_category_with_generated_unique_slug(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => 'Tableros eléctricos', 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));

        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => 'Tableros eléctricos'])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['slug' => 'tableros-electricos', 'is_active' => true]);
        $this->assertDatabaseHas('categories', ['slug' => 'tableros-electricos-2', 'is_active' => false]);
    }

    public function test_store_requires_name_and_a_root_category_as_parent(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $root->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), ['name' => '', 'parent_id' => $child->id])
            ->assertSessionHasErrors(['name', 'parent_id']);
    }

    public function test_update_changes_data_and_blocks_own_parent(): void
    {
        $category = Category::factory()->create(['name' => 'Viejo']);

        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), ['name' => 'Nuevo', 'is_active' => '1'])
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSame('Nuevo', $category->fresh()->name);

        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), ['name' => 'Nuevo', 'parent_id' => $category->id])
            ->assertSessionHasErrors('parent_id');
    }

    public function test_category_without_products_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status');

        $this->assertModelMissing($category);
    }

    public function test_category_with_products_cannot_be_deleted_and_shows_message(): void
    {
        $category = Category::factory()->create(['name' => 'Motores']);
        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'tiene 1 producto'));

        $this->assertModelExists($category);
    }

    public function test_category_with_only_soft_deleted_products_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id])->delete();

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $parent))
            ->assertSessionHas('error');

        $this->assertModelExists($parent);
    }

    public function test_comercial_cannot_manage_categories(): void
    {
        $comercial = User::factory()->comercial()->create();

        $this->actingAs($comercial)->get(route('admin.categories.create'))->assertForbidden();
        $this->actingAs($comercial)->post(route('admin.categories.store'), ['name' => 'X'])->assertForbidden();
    }
}
