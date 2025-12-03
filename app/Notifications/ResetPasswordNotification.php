<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $resetUrl = $this->resetUrl($notifiable);

        return (new MailMessage)
            ->subject(__('auth.reset_password_subject'))
            ->greeting(__('Hello!'))
            ->line(__('auth.reset_password_line1'))
            ->action(__('auth.reset_password_action'), $resetUrl)
            ->line(__('auth.reset_password_line2', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(__('auth.reset_password_line3'))
            ->salutation(__('Regards') . ",\n" . config('app.name'));
    }

    /**
     * Get the password reset URL for the given notifiable.
     */
    protected function resetUrl($notifiable): string
    {
        // Get frontend URL from config
        $frontendUrl = config('app.frontend_url');

        // Build frontend reset password URL with token and email
        return "{$frontendUrl}/reset-password?token={$this->token}&email={$notifiable->getEmailForPasswordReset()}";
    }
}
