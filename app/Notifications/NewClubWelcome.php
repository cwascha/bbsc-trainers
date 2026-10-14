<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewClubWelcome extends Notification
{
    use Queueable;

    public function __construct(
        private string $token,
        private string $clubName,
        private string $clubSlug,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Password reset routes require a club subdomain — build the URL manually
        $appDomain = config('app.domain', parse_url(config('app.url'), PHP_URL_HOST));
        $clubUrl   = 'https://' . $this->clubSlug . '.' . $appDomain;
        $resetUrl  = $clubUrl . '/reset-password/' . $this->token
                   . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Welcome to TrainerSync — Set up your account')
            ->markdown('emails.welcome-club', [
                'clubName' => $this->clubName,
                'clubUrl'  => $clubUrl,
                'resetUrl' => $resetUrl,
            ]);
    }
}
