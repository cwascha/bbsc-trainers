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

// Send the weekend trainer roster to admins every Friday at 6pm ET.
Schedule::command('roster:notify')
    ->fridays()
    ->at('18:00')
    ->timezone('America/New_York')
    ->name('send-weekend-roster')
    ->withoutOverlapping()
    ->description('Send upcoming weekend trainer roster via SMS to configured recipients');
