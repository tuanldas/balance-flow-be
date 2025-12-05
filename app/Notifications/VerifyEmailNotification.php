<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject(__('auth.verify_email_subject'))
            ->greeting(__('Hello!'))
            ->line(__('auth.verify_email_line1'))
            ->action(__('auth.verify_email_action'), $verificationUrl)
            ->line(__('auth.verify_email_line2'))
            ->salutation(__('Regards').",\n".config('app.name'));
    }

    /**
     * Get the verification URL for the given notifiable.
     */
    protected function verificationUrl($notifiable): string
    {
        // Get frontend URL from config
        $frontendUrl = config('app.frontend_url');

        // Generate verification hash
        $hash = sha1($notifiable->getEmailForVerification());

        // Build frontend verification URL
        return "{$frontendUrl}/verify-email?id={$notifiable->getKey()}&hash={$hash}";
    }
}
