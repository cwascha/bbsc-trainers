<?php

namespace Database\Seeders;

use App\Models\TrainingDay;
use Illuminate\Database\Seeder;

class TrainingDaySeeder extends Seeder
{
    public function run(): void
    {
        // Each entry: [date, weekend_number, session_start, session_end]
        // Weekends 1-2 (Apr 11-19): 11:30 AM - 2:30 PM (3 hrs)
        // Weekends 3-8 Saturdays:   9:30 AM  - 2:30 PM  (5 hrs)
        // Weekends 3-8 Sundays:     9:30 AM  - 12:30 PM (3 hrs)
        $days = [
            ['2026-04-11', 1, '11:30:00', '14:30:00'],
            ['2026-04-12', 1, '09:30:00', '12:30:00'],
            ['2026-04-18', 2, '11:30:00', '14:30:00'],
            ['2026-04-19', 2, '09:30:00', '12:30:00'],
            ['2026-04-25', 3, '09:30:00', '14:30:00'],
            ['2026-04-26', 3, '09:30:00', '12:30:00'],
            ['2026-05-02', 4, '09:30:00', '14:30:00'],
            ['2026-05-03', 4, '09:30:00', '12:30:00'],
            ['2026-05-09', 5, '09:30:00', '14:30:00'],
            ['2026-05-10', 5, '09:30:00', '12:30:00'],
            ['2026-05-16', 6, '09:30:00', '14:30:00'],
            ['2026-05-17', 6, '09:30:00', '12:30:00'],
            // No May 23-24 (Memorial Day Weekend)
            ['2026-05-30', 7, '09:30:00', '14:30:00'],
            ['2026-05-31', 7, '09:30:00', '12:30:00'],
            ['2026-06-06', 8, '09:30:00', '14:30:00'],
            ['2026-06-07', 8, '09:30:00', '12:30:00'],
        ];

        foreach ($days as [$date, $weekendNumber, $start, $end]) {
            TrainingDay::updateOrCreate(
                ['date' => $date],
                [
                    'weekend_number' => $weekendNumber,
                    'max_spots'      => 12,
                    'session_start'  => $start,
                    'session_end'    => $end,
                ]
            );
        }
    }
}
