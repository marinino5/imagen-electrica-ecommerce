<?php

namespace Tests\Feature\Api\V1;

use App\Enums\PriceSource;
use App\Enums\ProductUnit;
use App\Enums\SaleMode;
use App\Models\DatasheetDownload;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Ficha técnica con cotización en PDF: compra directa, precio de referencia y solo cotización; queda registrada. */
class DatasheetApiTest extends TestCase
{
    use RefreshDatabase;

    private function pdfText(string $binary): string
    {
        // dompdf escribe el texto en el flujo del PDF; para comprobarlo se busca en el binario ya descomprimido
        return $binary;
    }

    public function test_devuelve_un_pdf_y_lo_registra(): void
    {
        $product = Product::factory()->create(['sku' => 'CWB9-PRUEBA', 'sale_mode' => SaleMode::Direct, 'price' => 119000, 'tax_rate' => 19, 'variant_label' => '9 A', 'specs' => ['tipo' => 'Contactor', 'serie' => 'CWB', 'corriente_a' => 9]]);

        $response = $this->get('/api/v1/datasheet?sku=CWB9-PRUEBA&quantity=2');

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $response->assertHeader('X-Document-Number');

        $d = DatasheetDownload::firstOrFail();
        $this->assertSame($product->id, $d->product_id);
        $this->assertSame('CWB9-PRUEBA', $d->sku);
        $this->assertSame('2.00', $d->quantity);
        $this->assertSame('direct', $d->price_kind->value);
        $this->assertSame('238000.00', $d->total);
        $this->assertMatchesRegularExpression('/^IE-FT-\d{4}-\d{5}$/', $d->document_number);
        $this->assertSame($d->document_number, $response->headers->get('X-Document-Number'));
    }

    public function test_los_tres_tipos_de_precio_se_registran(): void
    {
        Product::factory()->create(['sku' => 'DIR', 'sale_mode' => SaleMode::Direct, 'price' => 1000]);
        Product::factory()->create(['sku' => 'REF', 'sale_mode' => SaleMode::Direct, 'price' => 2000, 'price_source' => PriceSource::Reference]);
        Product::factory()->create(['sku' => 'COT', 'sale_mode' => SaleMode::Quote, 'price' => null]);

        foreach (['DIR', 'REF', 'COT'] as $sku) {
            $this->get("/api/v1/datasheet?sku={$sku}")->assertOk();
        }

        $this->assertSame(['direct', 'reference', 'quote'], DatasheetDownload::orderBy('id')->get()->map(fn ($d) => $d->price_kind->value)->all());
        $this->assertNull(DatasheetDownload::where('sku', 'COT')->first()->total);
    }

    public function test_sku_con_barra_funciona_por_query(): void
    {
        Product::factory()->create(['sku' => '78192/TM-848-2']);

        $this->get('/api/v1/datasheet?sku='.urlencode('78192/TM-848-2'))->assertOk();
    }

    public function test_errores_404_y_422(): void
    {
        $this->getJson('/api/v1/datasheet?sku=NOEXISTE')->assertNotFound();
        $this->getJson('/api/v1/datasheet')->assertUnprocessable()->assertJsonValidationErrors('sku');

        $unit = Product::factory()->create(['sku' => 'UNI', 'unit' => ProductUnit::Unidad]);
        $this->getJson('/api/v1/datasheet?sku=UNI&quantity=1.5')->assertUnprocessable();
        $this->getJson('/api/v1/datasheet?sku=UNI&quantity=0')->assertUnprocessable();

        $inactive = Product::factory()->create(['sku' => 'OFF', 'is_active' => false]);
        $this->getJson('/api/v1/datasheet?sku=OFF')->assertNotFound();
        $this->assertDatabaseCount('datasheet_downloads', 0);
    }

    public function test_el_cable_acepta_metros_con_decimales(): void
    {
        Product::factory()->create(['sku' => 'CAB', 'unit' => ProductUnit::Metro, 'sale_mode' => SaleMode::Direct, 'price' => 3000]);

        $this->get('/api/v1/datasheet?sku=CAB&quantity=12.5')->assertOk();
        $this->assertSame('37500.00', DatasheetDownload::firstOrFail()->total);
    }

    public function test_el_panel_lista_las_fichas_descargadas(): void
    {
        Product::factory()->create(['sku' => 'LIST-1']);
        $this->get('/api/v1/datasheet?sku=LIST-1')->assertOk();

        $this->get(route('admin.datasheets.index'))->assertRedirect();
        $this->actingAs(User::factory()->comercial()->create());
        $this->get(route('admin.datasheets.index'))->assertOk()->assertSee('Fichas descargadas')->assertSee('LIST-1')->assertSee('IE-FT-');
        $this->get(route('admin.datasheets.index', ['q' => 'otra']))->assertOk()->assertDontSee('LIST-1');
    }
}
