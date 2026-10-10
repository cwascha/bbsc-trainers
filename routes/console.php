<?php

use App\Services\AssignmentService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-assignment disabled — assignments are now managed manually via the admin UI.
// Schedule::call(function () {
//     app(AssignmentService::class)->assignUpcomingWeekend();
// })->weeklyOn(4, '08:00') // 4 = Thursday
//   ->name('assign-upcoming-weekend')
//   ->withoutOverlapping()
//   ->description('Assign trainers to the upcoming weekend and send SMS notifications');

// Run every minute on Fridays; the command checks each club's timezone and sends at 6 PM local time.
Schedule::command('roster:notify --all-clubs')
    ->cron('* * * * 5')
    ->name('send-weekend-roster')
    ->withoutOverlapping()
    ->description('Send upcoming weekend trainer roster via SMS — per club timezone');
