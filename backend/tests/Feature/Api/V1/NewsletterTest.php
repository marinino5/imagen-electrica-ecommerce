<?php

namespace Tests\Feature\Api\V1;

use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_suscribe_y_guarda_el_consentimiento_con_fecha_e_ip(): void
    {
        $this->postJson('/api/v1/newsletter', ['email' => 'Laura@Empresa.co', 'privacy_accepted' => true])
            ->assertCreated()
            ->assertJsonPath('data.message', 'Listo, te suscribimos al boletín.');

        $this->assertDatabaseCount('newsletter_subscriptions', 1);
        $subscription = NewsletterSubscription::first();
        $this->assertSame('laura@empresa.co', $subscription->email);
        $this->assertTrue($subscription->consent);
        $this->assertNotNull($subscription->consented_at);
        $this->assertNotNull($subscription->ip);
    }

    public function test_sin_autorizacion_de_datos_responde_422(): void
    {
        $this->postJson('/api/v1/newsletter', ['email' => 'laura@empresa.co'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('privacy_accepted');

        $this->postJson('/api/v1/newsletter', ['email' => 'laura@empresa.co', 'privacy_accepted' => false])
            ->assertUnprocessable();

        $this->assertDatabaseCount('newsletter_subscriptions', 0);
    }

    public function test_valida_el_correo(): void
    {
        $this->postJson('/api/v1/newsletter', ['email' => 'no-es-correo', 'privacy_accepted' => true])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_un_correo_repetido_no_se_duplica_ni_cambia_el_consentimiento_original(): void
    {
        $this->postJson('/api/v1/newsletter', ['email' => 'laura@empresa.co', 'privacy_accepted' => true])->assertCreated();
        $first = NewsletterSubscription::first()->consented_at;

        $this->travel(1)->days();
        $this->postJson('/api/v1/newsletter', ['email' => 'LAURA@empresa.co', 'privacy_accepted' => true])->assertCreated();

        $this->assertDatabaseCount('newsletter_subscriptions', 1);
        $this->assertTrue(NewsletterSubscription::first()->consented_at->equalTo($first));
    }

    public function test_el_campo_trampa_responde_igual_pero_no_guarda(): void
    {
        $this->postJson('/api/v1/newsletter', ['email' => 'bot@spam.co', 'privacy_accepted' => true, 'website' => 'http://spam'])
            ->assertCreated();

        $this->assertDatabaseCount('newsletter_subscriptions', 0);
    }

    public function test_limita_los_intentos_por_minuto(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->postJson('/api/v1/newsletter', ['email' => "persona$i@empresa.co", 'privacy_accepted' => true])->assertCreated();
        }

        $this->postJson('/api/v1/newsletter', ['email' => 'persona6@empresa.co', 'privacy_accepted' => true])->assertStatus(429);
    }
}
