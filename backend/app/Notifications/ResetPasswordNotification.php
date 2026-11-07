<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Correo para restablecer la contraseña, con la marca de Imagen Eléctrica. El enlace apunta a la tienda
 * (/restablecer?token=…&email=…), no al panel: la API no tiene vistas de recuperación.
 */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);
        $minutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Restablece tu contraseña · '.config('app.name'))
            ->view(['mail.password-reset', 'mail.password-reset-text'], [
                'name' => $notifiable->name,
                'url' => $url,
                'minutes' => $minutes,
            ]);
    }

    protected function resetUrl($notifiable): string
    {
        return rtrim((string) config('app.store_url'), '/').'/restablecer?'.http_build_query([
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
