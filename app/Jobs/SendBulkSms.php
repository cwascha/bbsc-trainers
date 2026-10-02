<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(
        private readonly array $trainerIds,
        private readonly string $message,
    ) {}

    public function handle(SmsService $smsService): void
    {
        $trainers = User::whereIn('id', $this->trainerIds)->get();

        foreach ($trainers as $trainer) {
            if (! $trainer->phone) {
                continue;
            }
            try {
                $smsService->sendCustom($trainer, $this->message);
                usleep(200000);
            } catch (\Exception $e) {
                Log::error("SendBulkSms: failed to send to {$trainer->phone}: " . $e->getMessage());
            }
        }
    }
}
