<?php

namespace Tests\Feature\Api\V1;

use App\Enums\UserRole;
use App\Mail\FreeQuoteReceived;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class FreeQuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Laura Gómez',
            'phone' => '300 123 4567',
            'address' => 'Cra 18 No 24-32',
            'city' => 'Bucaramanga',
            'message' => 'Necesito 20 metros de cable 4x10 y un guardamotor.',
            'privacy_accepted' => true,
        ], $override);
    }

    protected function setUp(): void
    {
        parent::setUp();
        RateLimiter::clear('m:127.0.0.1');
        Mail::fake();
    }

    public function test_guarda_una_cotizacion_libre_sin_productos_y_avisa_por_correo(): void
    {
        $response = $this->postJson('/api/v1/quote-requests/free', $this->payload(['application' => 'Bomba de agua']))
            ->assertCreated()
            ->assertJsonPath('data.response_time', config('mail.quote_response_time'));

        $quote = QuoteRequest::firstOrFail();
        $response->assertJsonPath('data.reference', sprintf('LIB-%04d', $quote->id));
        $this->assertSame('free', $quote->type->value);
        $this->assertSame([], $quote->items);
        $this->assertNull($quote->email);
        $this->assertSame('Bomba de agua', $quote->application);
        $this->assertNull($quote->attachment_name);

        Mail::assertSent(FreeQuoteReceived::class, fn ($mail) => $mail->hasTo('imagenelectrica@gmail.com'));
    }

    public function test_guarda_el_adjunto_en_la_base_de_datos_y_lo_envia_en_el_correo(): void
    {
        $file = UploadedFile::fake()->create('placa.pdf', 200, 'application/pdf');

        $this->post('/api/v1/quote-requests/free', $this->payload(['attachment' => $file]), ['Accept' => 'application/json'])->assertCreated();

        $quote = QuoteRequest::with('attachment')->firstOrFail();
        $this->assertSame('placa.pdf', $quote->attachment_name);
        $this->assertSame('application/pdf', $quote->attachment_mime);
        $this->assertNotNull($quote->attachment);
        $this->assertSame(file_get_contents($file->getRealPath()), $quote->attachment->contents());
        Mail::assertSent(FreeQuoteReceived::class, fn ($mail) => count($mail->attachments()) === 1);
    }

    public function test_valida_los_campos_obligatorios_y_el_adjunto(): void
    {
        $this->postJson('/api/v1/quote-requests/free', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'address', 'city', 'message', 'privacy_accepted']);

        $this->post('/api/v1/quote-requests/free', $this->payload(['attachment' => UploadedFile::fake()->create('virus.exe', 10)]), ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('attachment');
        $this->post('/api/v1/quote-requests/free', $this->payload(['attachment' => UploadedFile::fake()->create('grande.pdf', 6000, 'application/pdf')]), ['Accept' => 'application/json'])
            ->assertStatus(422)->assertJsonValidationErrors('attachment');
        $this->postJson('/api/v1/quote-requests/free', $this->payload(['email' => 'no-es-correo']))->assertStatus(422)->assertJsonValidationErrors('email');

        $this->assertSame(0, QuoteRequest::count());
    }

    public function test_el_campo_trampa_no_guarda_ni_envia_nada(): void
    {
        $this->postJson('/api/v1/quote-requests/free', $this->payload(['website' => 'https://spam.example']))->assertCreated();

        $this->assertSame(0, QuoteRequest::count());
        Mail::assertNothingSent();
    }

    public function test_si_el_correo_falla_la_solicitud_queda_guardada(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP caído'));

        $this->postJson('/api/v1/quote-requests/free', $this->payload())->assertCreated();

        $this->assertSame(1, QuoteRequest::count());
    }

    public function test_limita_a_cinco_solicitudes_por_minuto_por_ip(): void
    {
        foreach (range(1, 5) as $i) {
            $this->postJson('/api/v1/quote-requests/free', $this->payload())->assertCreated();
        }

        $this->postJson('/api/v1/quote-requests/free', $this->payload())->assertStatus(429);
    }

    public function test_el_panel_muestra_la_etiqueta_y_descarga_el_adjunto(): void
    {
        $this->post('/api/v1/quote-requests/free', $this->payload(['attachment' => UploadedFile::fake()->image('tablero.png')]), ['Accept' => 'application/json'])->assertCreated();
        $quote = QuoteRequest::firstOrFail();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.quote-requests.index'))->assertOk()->assertSee('Cotización libre');
        $this->actingAs($admin)->get(route('admin.quote-requests.show', $quote))->assertOk()->assertSee('Descargar adjunto')->assertSee($quote->reference());
        $this->actingAs($admin)->get(route('admin.quote-requests.attachment', $quote))
            ->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="tablero.png"');

        $cliente = User::factory()->create(['role' => UserRole::Cliente]);
        $this->actingAs($cliente)->get(route('admin.quote-requests.attachment', $quote))->assertForbidden();
    }
}
