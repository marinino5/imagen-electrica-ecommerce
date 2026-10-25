<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

/** Los errores de la API salen en JSON y en español, sin detalles internos. */
class ErrorFormatTest extends TestCase
{
    public function test_unexpected_errors_return_a_generic_500_without_internals(): void
    {
        config(['app.debug' => false]);
        Route::get('api/v1/boom', fn () => throw new RuntimeException('detalle secreto'));

        $this->getJson('/api/v1/boom')
            ->assertStatus(500)
            ->assertExactJson(['message' => 'Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.']);
    }

    public function test_wrong_method_returns_405_in_spanish(): void
    {
        $this->postJson('/api/v1/products')->assertStatus(405)->assertJsonPath('message', 'Método no permitido para esta ruta.');
    }

    public function test_validation_errors_include_message_and_errors(): void
    {
        $this->postJson('/api/v1/login', [])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Escribe tu correo.')
            ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);
    }

    public function test_the_panel_keeps_its_own_error_pages(): void
    {
        $this->get('/admin/ruta-que-no-existe')->assertNotFound()->assertSee('404', false);
    }
}
