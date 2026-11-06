<?php

namespace Tests\Feature;

use App\Models\ProductLine;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\LineImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LineImageSeederTest extends TestCase
{
    use RefreshDatabase;

    private string $manifest;

    private ?string $original = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([BrandSeeder::class, CatalogSeeder::class]);
        $this->manifest = database_path('data/line-images.json');
        $this->original = file_get_contents($this->manifest);
    }

    protected function tearDown(): void
    {
        file_put_contents($this->manifest, $this->original);
        parent::tearDown();
    }

    public function test_llena_solo_las_imagenes_vacias_y_no_pisa_las_existentes(): void
    {
        ProductLine::where('slug', 'guardamotores')->update(['image_url' => '/img/productos/control/otra.webp']);
        file_put_contents($this->manifest, json_encode([
            'contactores' => '/img/productos/control/weg-contactores.webp',
            'guardamotores' => '/img/productos/control/weg-guardamotores.webp',
            'no-existe' => '/img/x.webp',
        ]));

        $this->seed(LineImageSeeder::class);

        $this->assertSame('/img/productos/control/weg-contactores.webp', ProductLine::where('slug', 'contactores')->value('image_url'));
        $this->assertSame('/img/productos/control/otra.webp', ProductLine::where('slug', 'guardamotores')->value('image_url'));
    }

    public function test_el_manifiesto_vacio_no_cambia_nada_y_es_idempotente(): void
    {
        $this->seed(LineImageSeeder::class);
        $this->seed(LineImageSeeder::class);

        $this->assertSame(0, ProductLine::whereNotNull('image_url')->count());
    }

    public function test_la_api_expone_image_url(): void
    {
        ProductLine::where('slug', 'contactores')->update(['image_url' => '/img/productos/control/weg-contactores.webp']);

        $line = collect($this->getJson('/api/v1/lines')->json('data'))->firstWhere('slug', 'contactores');

        $this->assertSame('/img/productos/control/weg-contactores.webp', $line['image_url']);
    }
}
