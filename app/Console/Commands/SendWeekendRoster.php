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
    protected $signature   = 'roster:notify {--dry-run : Print message without sending} {--club= : Club slug to send for (defaults to CLUB_SLUG env or first club)}';
    protected $description = 'Send the upcoming weekend trainer roster via SMS to configured recipients';

    public function handle(): int
    {
        // Resolve club — option > env > first club
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

        $club = currentClub();

        // Per-club phones from DB take priority; fall back to env var for backwards compatibility
        $rawPhones = $club?->roster_notify_phones ?: config('services.roster.notify_phones', '');

        $phones = collect(explode(',', $rawPhones))
            ->map(fn($p) => preg_replace('/\D/', '', trim($p)))
            ->filter(fn($p) => strlen($p) >= 10)
            ->values();

        if ($phones->isEmpty()) {
            $this->error('No recipient phones configured. Set roster notify phones in Club Settings or ROSTER_NOTIFY_PHONES in .env.');
            Log::error('roster:notify — no roster notify phones configured for club: ' . ($club?->slug ?? 'unknown'));
            return 1;
        }

        // Find the upcoming Saturday (or today if it's already Saturday)
        $saturday = now()->timezone('America/New_York')->startOfDay();
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

        $message = $this->buildMessage($days, $saturday, $sunday);

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

        Log::info("roster:notify — done. Sent: {$sent}, Failed: {$failed}. Weekend: {$saturday->toDateString()}");

        return $failed > 0 && $sent === 0 ? 1 : 0;
    }

    private function buildMessage($days, $saturday, $sunday): string
    {
        $weekendNum = $days->first()->weekend_number;
        $satLabel   = $saturday->format('M j');
        $sunLabel   = $sunday->format('M j');

        $lines = ["BBSC Weekend {$weekendNum} Roster ({$satLabel}–{$sunLabel})"];

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

        // Attendance link expires Monday night (3 days from Friday)
        $attendanceUrl = URL::signedRoute('attendance.show', ['weekend' => $weekendNum], now()->addDays(3));
        $lines[] = '';
        $lines[] = "Mark attendance: {$attendanceUrl}";

        return implode("\n", $lines);
    }
}
