<?php

namespace App\Mail;

use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrainerEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $emailSubject,
        public string $emailBody,
        public string $recipientName,
        public ?Club $club = null,
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = $this->club?->contact_email ?: config('mail.from.address');
        $fromName  = $this->club?->name ?: config('mail.from.name');

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.trainer-email',
            with: ['club' => $this->club],
        );
    }
}
