<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductLine;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** La migración que divide «Motores de otras marcas» actúa sobre una base ya publicada con la línea antigua. */
class SplitOtherBrandMotorsMigrationTest extends TestCase
{
    use RefreshDatabase;

    /** Deja la base como estaba en producción: una sola línea con las referencias de cuatro marcas y la foto de WEG. */
    private function oldState(): ProductLine
    {
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);

        $old = ProductLine::where('slug', 'motor-trifasico-weg-de-bobinado')->firstOrFail();
        $old->update(['name' => 'Motores de otras marcas', 'slug' => 'motores-de-otras-marcas', 'brand_label' => 'ABB · Match Drive · Nidec · Nord · WEG']);
        foreach (['motores-abb', 'motores-nidec-us-motors', 'motores-nord', 'motores-match-drive'] as $slug) {
            $line = ProductLine::where('slug', $slug)->firstOrFail();
            Product::where('line_id', $line->id)->update(['line_id' => $old->id, 'image_url' => '/img/catalogo/weg-w22-lateral.webp']);
            $line->delete();
        }

        return $old;
    }

    private function migrar(): void
    {
        (require database_path('migrations/2026_11_10_130000_split_other_brand_motors.php'))->up();
    }

    public function test_reparte_las_referencias_por_marca_y_quita_la_foto_de_weg(): void
    {
        $this->oldState();
        $this->migrar();

        $this->assertSame(8, Product::whereIn('line_id', ProductLine::whereIn('slug', ['motores-abb', 'motores-nidec-us-motors', 'motores-nord', 'motores-match-drive', 'motor-trifasico-weg-de-bobinado'])->pluck('id'))->count());
        $this->assertSame(3, ProductLine::where('slug', 'motores-nord')->firstOrFail()->variants()->count());
        $this->assertSame(2, ProductLine::where('slug', 'motores-match-drive')->firstOrFail()->variants()->count());
        $this->assertSame('WEG', ProductLine::where('slug', 'motor-trifasico-weg-de-bobinado')->firstOrFail()->brand_label);

        // Ninguna referencia de otra marca conserva la foto de WEG
        $weg = Brand::where('slug', 'weg')->firstOrFail();
        $this->assertSame(0, Product::where('brand_id', '!=', $weg->id)->where('image_url', '/img/catalogo/weg-w22-lateral.webp')->count());
        $this->assertNull(ProductLine::where('slug', 'motores-match-drive')->firstOrFail()->image_url);
    }

    public function test_es_idempotente(): void
    {
        $this->oldState();
        $this->migrar();
        $this->migrar();

        $this->assertSame(1, ProductLine::where('slug', 'motores-abb')->count());
        $this->assertSame(3, ProductLine::where('slug', 'motores-nord')->firstOrFail()->variants()->count());
    }
}
