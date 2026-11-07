<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const GENERIC = 'Si el correo existe, te enviamos un enlace para restablecer tu contraseña.';

    private function client(array $overrides = []): User
    {
        return User::factory()->create(array_merge(['email' => 'laura@constructoraaltos.test', 'password' => 'ClaveVieja2026'], $overrides));
    }

    /** Pide el enlace y devuelve el token tal como llegó en la notificación. */
    private function requestToken(User $user): string
    {
        Notification::fake();
        $this->postJson('/api/v1/password/forgot', ['email' => $user->email])->assertOk();

        $token = null;
        Notification::assertSentTo($user, ResetPasswordNotification::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        return $token;
    }

    public function test_forgot_sends_a_branded_mail_with_a_link_to_the_store(): void
    {
        Notification::fake();
        $user = $this->client();

        $this->postJson('/api/v1/password/forgot', ['email' => 'LAURA@constructoraaltos.test '])
            ->assertOk()
            ->assertExactJson(['message' => self::GENERIC]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $n) use ($user) {
            $mail = $n->toMail($user);
            $expectedStart = rtrim(config('app.store_url'), '/').'/restablecer?token='.$n->token.'&email='.urlencode($user->email);

            $this->assertStringContainsString('Imagen Eléctrica', $mail->subject);
            $html = view($mail->view[0], $mail->viewData)->render();
            $this->assertStringContainsString('Imagen', $html);
            $this->assertStringContainsString('60 minutos', $html);
            $this->assertStringContainsString(htmlspecialchars($expectedStart, ENT_QUOTES), $html);

            return true;
        });
    }

    public function test_forgot_answers_the_same_for_an_unknown_email_and_sends_nothing(): void
    {
        Notification::fake();

        $this->postJson('/api/v1/password/forgot', ['email' => 'nadie@ejemplo.test'])
            ->assertOk()
            ->assertExactJson(['message' => self::GENERIC]);

        Notification::assertNothingSent();
    }

    public function test_forgot_sends_nothing_to_an_inactive_user_but_answers_the_same(): void
    {
        Notification::fake();
        $user = $this->client();
        $user->is_active = false;
        $user->save();

        $this->postJson('/api/v1/password/forgot', ['email' => $user->email])->assertOk()->assertExactJson(['message' => self::GENERIC]);

        Notification::assertNothingSent();
    }

    public function test_forgot_validates_the_email_format(): void
    {
        $this->postJson('/api/v1/password/forgot', ['email' => 'no-es-correo'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_forgot_is_rate_limited_per_ip(): void
    {
        RateLimiter::clear('m:127.0.0.1');
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/password/forgot', ['email' => "x{$i}@ejemplo.test"])->assertOk();
        }

        $this->postJson('/api/v1/password/forgot', ['email' => 'x6@ejemplo.test'])->assertStatus(429);
    }

    public function test_reset_changes_the_password_and_the_new_one_works_for_login(): void
    {
        $user = $this->client();
        $token = $this->requestToken($user);

        $this->postJson('/api/v1/password/reset', [
            'token' => $token, 'email' => $user->email, 'password' => 'ClaveNueva2026', 'password_confirmation' => 'ClaveNueva2026',
        ])->assertOk()->assertJsonPath('message', 'Cambiaste tu contraseña. Ya puedes ingresar.');

        $this->assertTrue(Hash::check('ClaveNueva2026', $user->fresh()->password));
        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'ClaveNueva2026'])->assertOk();
        $this->postJson('/api/v1/login', ['email' => $user->email, 'password' => 'ClaveVieja2026'])->assertUnauthorized();
    }

    public function test_the_link_works_only_once(): void
    {
        $user = $this->client();
        $token = $this->requestToken($user);
        $payload = ['token' => $token, 'email' => $user->email, 'password' => 'ClaveNueva2026', 'password_confirmation' => 'ClaveNueva2026'];

        $this->postJson('/api/v1/password/reset', $payload)->assertOk();
        $this->postJson('/api/v1/password/reset', array_merge($payload, ['password' => 'OtraClave2026x', 'password_confirmation' => 'OtraClave2026x']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('token');

        $this->assertTrue(Hash::check('ClaveNueva2026', $user->fresh()->password));
    }

    public function test_the_link_expires_after_60_minutes(): void
    {
        $user = $this->client();
        $token = $this->requestToken($user);

        $this->travel(59)->minutes();
        $this->assertTrue(Password::tokenExists($user, $token));

        $this->travel(2)->minutes();
        $this->postJson('/api/v1/password/reset', [
            'token' => $token, 'email' => $user->email, 'password' => 'ClaveNueva2026', 'password_confirmation' => 'ClaveNueva2026',
        ])->assertUnprocessable()->assertJsonValidationErrors('token');

        $this->assertTrue(Hash::check('ClaveVieja2026', $user->fresh()->password));
    }

    public function test_reset_rejects_a_wrong_token_or_a_different_email(): void
    {
        $user = $this->client();
        $other = $this->client(['email' => 'otra@ejemplo.test']);
        $token = $this->requestToken($user);
        $good = ['password' => 'ClaveNueva2026', 'password_confirmation' => 'ClaveNueva2026'];

        $this->postJson('/api/v1/password/reset', ['token' => 'token-falso', 'email' => $user->email] + $good)->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->postJson('/api/v1/password/reset', ['token' => $token, 'email' => $other->email] + $good)->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->assertTrue(Hash::check('ClaveVieja2026', $user->fresh()->password));
    }

    public function test_reset_applies_the_same_password_rules_as_registration(): void
    {
        $user = $this->client();
        $token = $this->requestToken($user);

        $this->postJson('/api/v1/password/reset', ['token' => $token, 'email' => $user->email, 'password' => 'corta', 'password_confirmation' => 'corta'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson('/api/v1/password/reset', ['token' => $token, 'email' => $user->email, 'password' => 'ClaveNueva2026', 'password_confirmation' => 'Distinta2026'])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson('/api/v1/password/reset', ['token' => $token, 'email' => $user->email, 'password' => str_repeat('a', 101), 'password_confirmation' => str_repeat('a', 101)])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_reset_revokes_the_api_tokens_of_the_account(): void
    {
        $user = $this->client();
        $plain = $user->createToken('api')->plainTextToken;
        $token = $this->requestToken($user);
        $this->withToken($plain)->getJson('/api/v1/me')->assertOk();

        $this->postJson('/api/v1/password/reset', ['token' => $token, 'email' => $user->email, 'password' => 'ClaveNueva2026', 'password_confirmation' => 'ClaveNueva2026'])->assertOk();

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_the_default_broker_notification_is_replaced_by_the_branded_one(): void
    {
        $user = $this->client();
        Notification::fake();
        Password::sendResetLink(['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
        Notification::assertNotSentTo($user, ResetPassword::class, fn ($n) => ! $n instanceof ResetPasswordNotification);
    }
}
