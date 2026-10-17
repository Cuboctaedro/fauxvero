<?php

namespace App\Notifications;

use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to users created from the admin panel. Uses the same token and
 * reset page as a password reset, with welcome wording.
 */
class SetPassword extends ResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Set your password')
            ->line('An account was created for you on '.config('app.name').'.')
            ->action('Set password', $url)
            ->line('This link will expire in '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' minutes. If it has expired, ask an admin to send a new one.');
    }
}
