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
        $baseUrl    = 'https://' . $this->clubSlug . '.' . $appDomain;
        $url        = $baseUrl . '/reset-password/' . $this->token
                    . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Welcome to TrainerSync — Set up your account')
            ->greeting('Welcome to TrainerSync!')
            ->line("Your **{$this->clubName}** account is ready. TrainerSync handles trainer scheduling, availability, SMS assignments, and payroll — all in one place.")
            ->line('To get started, set a password for your account:')
            ->action('Set Your Password', $url)
            ->line('This link expires in 60 minutes. If you need a new one, visit your club login page and use "Forgot password".')
            ->salutation('— The TrainerSync Team');
    }
}
