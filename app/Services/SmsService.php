<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\TrainingPlan;
use App\Models\TrainingDay;
use App\Models\User;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SmsService
{
    private ?Client $twilio = null;

    private function client(): ?Client
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');

        if (! $sid || ! $token) {
            return null;
        }

        if (! $this->twilio) {
            $this->twilio = new Client($sid, $token);
        }

        return $this->twilio;
    }

    public function sendAssignmentNotification(User $user, TrainingDay $day): void
    {
        if (! $user->phone) {
            return;
        }

        $date    = $day->date->format('l, F j, Y');
        $message = "Hi {$user->name}! You've been assigned to work at BBSC on {$date} from {$day->session_time_range}. ";
        $message .= $this->planLinksText($day);
        $message .= "Reply YES to confirm or NO to cancel.";

        $this->send($user, $day, $message);
    }

    public function sendReassignmentNotification(User $user, TrainingDay $day): void
    {
        if (! $user->phone) {
            return;
        }

        $date    = $day->date->format('l, F j, Y');
        $message = "Hi {$user->name}! A spot has opened up at BBSC on {$date} from {$day->session_time_range} and you've been assigned. ";
        $message .= $this->planLinksText($day);
        $message .= "Reply YES to confirm or NO to cancel.";

        $this->send($user, $day, $message);
    }

    /**
     * Build the plan link portion of an SMS for a given training day.
     * Saturday gets both the main (K/1st) and Sparks plans if available.
     * Sunday gets only the main plan.
     */
    private function planLinksText(TrainingDay $day): string
    {
        $isSaturday = $day->date->isSaturday();
        $text       = '';

        $mainPlan = TrainingPlan::where('weekend_number', $day->weekend_number)
            ->where('program', 'main')
            ->first();

        if ($mainPlan) {
            $url  = URL::signedRoute('training-plans.view-signed', ['trainingPlan' => $mainPlan->id], now()->addDays(7));
            $text .= $isSaturday ? "K/1st plan: {$url} " : "Training plan: {$url} ";
        }

        if ($isSaturday) {
            $sparksPlan = TrainingPlan::where('weekend_number', $day->weekend_number)
                ->where('program', 'sparks')
                ->first();
            if ($sparksPlan) {
                $url  = URL::signedRoute('training-plans.view-signed', ['trainingPlan' => $sparksPlan->id], now()->addDays(7));
                $text .= "Sparks plan: {$url} ";
            }
        }

        return $text;
    }

    public function sendCustom(User $user, string $message): void
    {
        if (! $user->phone) return;

        $phone  = $this->formatPhone($user->phone);
        $sid    = null;
        $status = 'sent';
        $client = $this->client();

        if ($client) {
            try {
                $msg    = $client->messages->create($phone, [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                ]);
                $sid    = $msg->sid;
                $status = $sid ? 'sent' : $msg->status;
            } catch (\Exception $e) {
                Log::error('Twilio SMS failed: ' . $e->getMessage());
                $status = 'failed';
            }
        } else {
            Log::info("SMS (Twilio not configured) to {$phone}: {$message}");
        }

        NotificationLog::create([
            'user_id'         => $user->id,
            'training_day_id' => null,
            'type'            => 'custom',
            'phone'           => $phone,
            'message'         => $message,
            'twilio_sid'      => $sid,
            'status'          => $status,
            'sent_at'         => now(),
        ]);
    }

    private function send(User $user, TrainingDay $day, string $message): void
    {
        $phone = $this->formatPhone($user->phone);
        $sid = null;
        $status = 'sent';

        $client = $this->client();

        if ($client) {
            try {
                $msg = $client->messages->create($phone, [
                    'from' => config('services.twilio.from'),
                    'body' => $message,
                ]);
                $sid = $msg->sid;
                // Twilio initially returns "queued" — if we have a SID the message
                // was accepted successfully, so we log it as "sent".
                $status = $sid ? 'sent' : $msg->status;
            } catch (\Exception $e) {
                Log::error('Twilio SMS failed: ' . $e->getMessage());
                $status = 'failed';
            }
        } else {
            Log::info("SMS (Twilio not configured) to {$phone}: {$message}");
        }

        NotificationLog::create([
            'user_id'         => $user->id,
            'training_day_id' => $day->id,
            'type'            => 'assignment',
            'phone'           => $phone,
            'message'         => $message,
            'twilio_sid'      => $sid,
            'status'          => $status,
            'sent_at'         => now(),
        ]);
    }

    private function formatPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }
        return '+' . $digits;
    }
}
