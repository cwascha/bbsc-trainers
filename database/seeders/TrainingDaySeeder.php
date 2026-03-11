<?php

namespace Database\Seeders;

use App\Models\TrainingDay;
use Illuminate\Database\Seeder;

class TrainingDaySeeder extends Seeder
{
    public function run(): void
    {
        $weekends = [
            1 => ['2026-04-11', '2026-04-12'],
            2 => ['2026-04-18', '2026-04-19'],
            3 => ['2026-04-25', '2026-04-26'],
            4 => ['2026-05-02', '2026-05-03'],
            5 => ['2026-05-09', '2026-05-10'],
            6 => ['2026-05-16', '2026-05-17'],
            // No weekend 7 (May 23-24 — Memorial Day Weekend)
            7 => ['2026-05-30', '2026-05-31'],
            8 => ['2026-06-06', '2026-06-07'],
        ];

        foreach ($weekends as $weekendNumber => $dates) {
            foreach ($dates as $date) {
                TrainingDay::firstOrCreate(
                    ['date' => $date],
                    [
                        'weekend_number' => $weekendNumber,
                        'max_spots'      => 12,
                        'session_start'  => '08:30:00',
                        'session_end'    => '15:30:00',
                    ]
                );
            }
        }
    }
}
