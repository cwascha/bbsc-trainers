<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\TrainingDay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Twilio\Rest\Client;

class SendWeekendRoster extends Command
{
    protected $signature   = 'roster:notify
                                {--dry-run : Print message without sending}
                                {--club= : Club slug to send for (defaults to CLUB_SLUG env or first club)}
                                {--all-clubs : Iterate all active clubs and send for any whose 6 PM window has arrived}';
    protected $description = 'Send the upcoming weekend trainer roster via SMS to configured recipients';

    public function handle(): int
    {
        if ($this->option('all-clubs')) {
            return $this->handleAllClubs();
        }

        return $this->handleSingleClub();
    }

    private function handleAllClubs(): int
    {
        $clubs = Club::whereIn('subscription_status', ['active', 'trial'])->get();
        $errors = 0;

        foreach ($clubs as $club) {
            $tz  = $club->timezone ?: 'America/New_York';
            $now = now()->timezone($tz);

            // Only send on Fridays between 18:00–18:04 in the club's timezone
            if ($now->dayOfWeek !== 5 || $now->hour !== 18 || $now->minute > 4) {
                continue;
            }

            // Prevent double-sending
            if ($club->roster_last_sent_at && $club->roster_last_sent_at->timezone($tz)->isToday()) {
                $this->info("Already sent for {$club->slug} today, skipping.");
                continue;
            }

            $this->info("Sending roster for club: {$club->slug} ({$tz})");
            app()->instance('currentClub', $club);

            $result = $this->sendForClub($club);
            if ($result !== 0) {
                $errors++;
            }
        }

        return $errors > 0 ? 1 : 0;
    }

    private function handleSingleClub(): int
    {
        if (! currentClub()) {
            $slug = $this->option('club') ?? config('app.club_slug');
            $club = $slug
                ? Club::where('slug', $slug)->first()
                : Club::first();

            if (! $club) {
                $this->error('No club found. Pass --club=<slug> or set CLUB_SLUG in .env.');
                Log::error('roster:notify — no club found');
                return 1;
            }
            app()->instance('currentClub', $club);
        }

        return $this->sendForClub(currentClub());
    }

    private function sendForClub(Club $club): int
    {
        $rawPhones = $club->roster_notify_phones ?: config('services.roster.notify_phones', '');

        $phones = collect(explode(',', $rawPhones))
            ->map(fn($p) => preg_replace('/\D/', '', trim($p)))
            ->filter(fn($p) => strlen($p) >= 10)
            ->values();

        if ($phones->isEmpty()) {
            $this->error("No recipient phones configured for club: {$club->slug}");
            Log::error('roster:notify — no roster notify phones configured for club: ' . $club->slug);
            return 1;
        }

        $tz       = $club->timezone ?: 'America/New_York';
        $saturday = now()->timezone($tz)->startOfDay();
        while ($saturday->dayOfWeek !== 6) {
            $saturday->addDay();
        }
        $sunday = $saturday->copy()->addDay();

        $days = TrainingDay::whereBetween('date', [$saturday->toDateString(), $sunday->toDateString()])
            ->with(['availabilities' => fn($q) => $q->whereIn('status', ['assigned', 'confirmed'])->with('user')])
            ->orderBy('date')
            ->orderBy('session_start')
            ->get();

        if ($days->isEmpty()) {
            $this->info('No training days found for the upcoming weekend (' . $saturday->toDateString() . ').');
            Log::info('roster:notify — no training days found for ' . $saturday->toDateString());
            return 0;
        }

        $message = $this->buildMessage($days, $saturday, $sunday, $club);

        $this->line($message);

        if ($this->option('dry-run')) {
            $this->info('Dry run — no SMS sent.');
            return 0;
        }

        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.from');

        if (! $sid || ! $token) {
            $this->error('Twilio not configured.');
            return 1;
        }

        $twilio = new Client($sid, $token);
        $sent   = 0;
        $failed = 0;

        foreach ($phones as $phone) {
            $e164 = '+1' . substr($phone, -10);
            try {
                $twilio->messages->create($e164, ['from' => $from, 'body' => $message]);
                $this->info("Sent to {$e164}");
                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send to {$e164}: " . $e->getMessage());
                Log::error("roster:notify — Twilio error for {$e164}: " . $e->getMessage());
                $failed++;
            }
        }

        Log::info("roster:notify — done for {$club->slug}. Sent: {$sent}, Failed: {$failed}. Weekend: {$saturday->toDateString()}");

        $result = $failed > 0 && $sent === 0 ? 1 : 0;

        if ($result === 0 && ! $this->option('dry-run')) {
            $club->update(['roster_last_sent_at' => now()]);
        }

        return $result;
    }

    private function buildMessage($days, $saturday, $sunday, Club $club): string
    {
        $weekendNum = $days->first()->weekend_number;
        $satLabel   = $saturday->format('M j');
        $sunLabel   = $sunday->format('M j');

        $lines = ["{$club->name} Weekend {$weekendNum} Roster ({$satLabel}–{$sunLabel})"];

        foreach ($days as $day) {
            $dayName  = $day->date->isSaturday() ? 'SAT ' . $day->date->format('M j') : 'SUN ' . $day->date->format('M j');
            $timeRange = \Carbon\Carbon::parse($day->session_start)->format('g:iA')
                       . '–' . \Carbon\Carbon::parse($day->session_end)->format('g:iA');

            $trainers = $day->availabilities
                ->map(fn($a) => $a->user->name)
                ->sort()
                ->values()
                ->join(', ');

            $lines[] = '';
            $lines[] = "{$dayName} {$day->program} ({$timeRange}):";
            $lines[] = $trainers ?: 'None assigned';
        }

        $attendanceUrl = URL::signedRoute('attendance.show', ['weekend' => $weekendNum], now()->addDays(3));
        $lines[] = '';
        $lines[] = "Mark attendance: {$attendanceUrl}";

        return implode("\n", $lines);
    }
}
