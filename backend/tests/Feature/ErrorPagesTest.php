<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/** Con APP_DEBUG=false un error inesperado muestra la página 500 propia y nunca el detalle de Laravel. */
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);
        Route::get('/prueba-error', fn () => throw new \RuntimeException('detalle-secreto-interno'));
        Route::middleware('api')->get('/api/prueba-error', fn () => throw new \RuntimeException('detalle-secreto-interno'));
    }

    public function test_a_server_error_shows_the_custom_500_page(): void
    {
        $this->get('/prueba-error')
            ->assertStatus(500)
            ->assertSee('Error del servidor')
            ->assertDontSee('detalle-secreto-interno')
            ->assertDontSee('Whoops')
            ->assertDontSee('RuntimeException');
    }

    public function test_the_api_hides_the_detail_of_a_server_error(): void
    {
        $this->getJson('/api/prueba-error')
            ->assertStatus(500)
            ->assertExactJson(['message' => 'Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.']);
    }

    public function test_an_unknown_page_shows_the_custom_404(): void
    {
        $this->get('/no-existe')->assertNotFound()->assertSee('Página no encontrada');
    }
}
