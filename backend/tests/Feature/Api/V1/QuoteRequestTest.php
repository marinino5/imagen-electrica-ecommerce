<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\ProductLine;
use App\Models\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class QuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        $product = Product::factory()->create();

        return array_merge([
            'name' => 'Laura Gómez',
            'phone' => '300 123 4567',
            'email' => 'laura@empresa.co',
            'city' => 'Bucaramanga',
            'company' => 'Constructora Demo',
            'message' => 'Necesito 3 motores.',
            'privacy_accepted' => true,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ], $override);
    }

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('m:127.0.0.1');
    }

    public function test_guarda_la_cotizacion_sin_token_con_copia_de_los_renglones(): void
    {
        $product = Product::factory()->create(['name' => 'Motor de prueba', 'sku' => 'T-1', 'variant_label' => '5 HP']);

        $this->postJson('/api/v1/quote-requests', $this->payload(['items' => [['product_id' => $product->id, 'quantity' => 3]]]))
            ->assertCreated()
            ->assertJsonPath('data.message', 'Recibimos tu solicitud. Un asesor te responderá pronto.');

        $quote = QuoteRequest::firstOrFail();
        $this->assertSame('Laura Gómez', $quote->name);
        $this->assertSame('new', $quote->status->value);
        $this->assertSame('T-1', $quote->items[0]['sku']);
        $this->assertSame('5 HP', $quote->items[0]['label']);
        $this->assertEquals(3, $quote->items[0]['quantity']);
        $this->assertNotNull($quote->privacy_accepted_at);
    }

    public function test_acepta_una_linea_bajo_pedido_sin_referencia(): void
    {
        $category = \App\Models\Category::factory()->create();
        ProductLine::create(['category_id' => $category->id, 'name' => 'Reductor WCG20', 'slug' => 'reductor-wcg20', 'sale_mode' => 'on_request']);

        $this->postJson('/api/v1/quote-requests', $this->payload(['items' => [['line' => 'reductor-wcg20', 'quantity' => 1]]]))->assertCreated();

        $this->assertSame('Reductor WCG20', QuoteRequest::firstOrFail()->items[0]['name']);
        $this->assertNull(QuoteRequest::firstOrFail()->items[0]['sku']);
    }

    public function test_valida_los_datos_obligatorios(): void
    {
        $this->postJson('/api/v1/quote-requests', ['items' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'email', 'city', 'privacy_accepted', 'items']);

        $this->postJson('/api/v1/quote-requests', $this->payload(['privacy_accepted' => false]))->assertStatus(422)->assertJsonValidationErrors('privacy_accepted');
        $this->postJson('/api/v1/quote-requests', $this->payload(['phone' => 'abc']))->assertStatus(422)->assertJsonValidationErrors('phone');
        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_cada_renglon_necesita_referencia_o_linea(): void
    {
        $this->postJson('/api/v1/quote-requests', $this->payload(['items' => [['quantity' => 2]]]))->assertStatus(422);
    }

    public function test_el_campo_trampa_no_guarda_nada_pero_responde_igual(): void
    {
        $this->postJson('/api/v1/quote-requests', $this->payload(['website' => 'https://spam.example']))->assertCreated();

        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_limita_a_cinco_solicitudes_por_minuto_por_ip(): void
    {
        $payload = $this->payload();

        foreach (range(1, 5) as $i) {
            $this->postJson('/api/v1/quote-requests', $payload)->assertCreated();
        }

        $this->postJson('/api/v1/quote-requests', $payload)->assertStatus(429);
    }
}
