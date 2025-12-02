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
            ->subject('Xác Thực Địa Chỉ Email')
            ->greeting('Xin chào!')
            ->line('Vui lòng nhấp vào nút bên dưới để xác thực địa chỉ email của bạn.')
            ->action('Xác Thực Email', $verificationUrl)
            ->line('Nếu bạn không tạo tài khoản, vui lòng bỏ qua email này.');
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
