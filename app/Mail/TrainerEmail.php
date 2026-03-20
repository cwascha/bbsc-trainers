<?php

namespace App\Mail;

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
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('chris@bbscsoccer.com', 'BBSC'),
            subject: $this->emailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.trainer-email');
    }
}
