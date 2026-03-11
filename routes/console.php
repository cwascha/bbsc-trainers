<?php

use App\Services\AssignmentService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run every Thursday at 8:00 AM to assign trainers for the upcoming weekend
// and send SMS notifications to assigned trainers.
Schedule::call(function () {
    app(AssignmentService::class)->assignUpcomingWeekend();
})->weeklyOn(4, '08:00') // 4 = Thursday
  ->name('assign-upcoming-weekend')
  ->withoutOverlapping()
  ->description('Assign trainers to the upcoming weekend and send SMS notifications');
