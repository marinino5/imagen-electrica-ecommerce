<?php

namespace Tests\Unit;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\BulkUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_only_the_listed_rows_and_columns_in_one_batch(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $a = Product::factory()->create(['sku' => '47040', 'variant_label' => 'a', 'category_id' => $category->id]);
        $b = Product::factory()->create(['sku' => '47040A', 'variant_label' => 'b', 'category_id' => $category->id]);
        $c = Product::factory()->create(['sku' => 'X1', 'variant_label' => 'c', 'name' => 'No se toca']);

        $sent = BulkUpdate::run('products', 'sku', [
            '47040' => ['variant_label' => 'nuevo A', 'brand_id' => $brand->id, 'specs' => null],
            'X1' => ['variant_label' => null, 'brand_id' => $brand->id, 'specs' => '{"potencia_hp":5}'],
        ]);

        $this->assertSame(2, $sent);
        $this->assertSame('nuevo A', $a->fresh()->variant_label);
        $this->assertSame('b', $b->fresh()->variant_label, 'un SKU numérico no debe afectar a «47040A»');
        $this->assertNull($c->fresh()->variant_label);
        $this->assertSame('No se toca', $c->fresh()->name);
        $this->assertSame(['potencia_hp' => 5], $c->fresh()->specs);
    }

    public function test_an_empty_list_sends_nothing(): void
    {
        $this->assertSame(0, BulkUpdate::run('products', 'sku', []));
    }
}
