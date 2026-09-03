<?php

use App\Models\TrainingDay;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Fall 2026: Sept 12 – Nov 1
        // Saturdays wk 9-14  (Sept 12 – Oct 17): 9:00 AM – 3:00 PM, 12 spots
        // Saturdays wk 15-16 (Oct 24, Oct 31):   12:30 PM – 3:00 PM, 12 spots
        // Sundays   wk 9-16  (Sept 13 – Nov 1):  10:00 AM – 12:30 PM, 6 spots

        $days = [
            // Weekend 9
            ['2026-09-12', 9,  '09:00:00', '15:00:00', 12],
            ['2026-09-13', 9,  '10:00:00', '12:30:00', 6],
            // Weekend 10
            ['2026-09-19', 10, '09:00:00', '15:00:00', 12],
            ['2026-09-20', 10, '10:00:00', '12:30:00', 6],
            // Weekend 11
            ['2026-09-26', 11, '09:00:00', '15:00:00', 12],
            ['2026-09-27', 11, '10:00:00', '12:30:00', 6],
            // Weekend 12
            ['2026-10-03', 12, '09:00:00', '15:00:00', 12],
            ['2026-10-04', 12, '10:00:00', '12:30:00', 6],
            // Weekend 13
            ['2026-10-10', 13, '09:00:00', '15:00:00', 12],
            ['2026-10-11', 13, '10:00:00', '12:30:00', 6],
            // Weekend 14
            ['2026-10-17', 14, '09:00:00', '15:00:00', 12],
            ['2026-10-18', 14, '10:00:00', '12:30:00', 6],
            // Weekend 15 — Saturday shifts to 12:30 PM start
            ['2026-10-24', 15, '12:30:00', '15:00:00', 12],
            ['2026-10-25', 15, '10:00:00', '12:30:00', 6],
            // Weekend 16
            ['2026-10-31', 16, '12:30:00', '15:00:00', 12],
            ['2026-11-01', 16, '10:00:00', '12:30:00', 6],
        ];

        foreach ($days as [$date, $weekendNumber, $start, $end, $spots]) {
            TrainingDay::updateOrCreate(
                ['date' => $date],
                [
                    'weekend_number' => $weekendNumber,
                    'session_start'  => $start,
                    'session_end'    => $end,
                    'max_spots'      => $spots,
                ]
            );
        }
    }

    public function down(): void
    {
        TrainingDay::whereIn('date', [
            '2026-09-12', '2026-09-13',
            '2026-09-19', '2026-09-20',
            '2026-09-26', '2026-09-27',
            '2026-10-03', '2026-10-04',
            '2026-10-10', '2026-10-11',
            '2026-10-17', '2026-10-18',
            '2026-10-24', '2026-10-25',
            '2026-10-31', '2026-11-01',
        ])->delete();
    }
};
