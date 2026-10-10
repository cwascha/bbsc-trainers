<?php

namespace App\Jobs;

use App\Mail\TrainerEmail;
use App\Models\Club;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTrainerEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly string $toEmail,
        public readonly string $toName,
        public readonly string $subject,
        public readonly string $body,
        public readonly ?int $clubId = null,
    ) {}

    public function handle(): void
    {
        $club = $this->clubId ? Club::find($this->clubId) : null;

        Mail::to($this->toEmail, $this->toName)
            ->send(new TrainerEmail($this->subject, $this->body, $this->toName, $club));
    }
}
