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
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

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
