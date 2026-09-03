<?php

use App\Models\TrainingDay;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add program column and replace date-unique with (date, program) unique
        Schema::table('training_days', function (Blueprint $table) {
            $table->string('program')->nullable()->after('date');
            $table->dropUnique(['date']);
            $table->unique(['date', 'program']);
        });

        // 2. Delete the placeholder fall days inserted by the previous migration
        TrainingDay::where('weekend_number', '>=', 9)->delete();

        // 3. Re-insert fall 2026 with proper per-program sessions
        //
        // Wk 9-14 Saturdays: Sparks 9:00-12:00 (10 spots) + Kindergarten 12:30-15:00 (12 spots)
        // Wk 15-16 Saturdays: Kindergarten only 12:30-15:00 (12 spots) — no Sparks
        // All Sundays wk 9-16: 1st Grade 10:00-12:30 (6 spots)

        $days = [
            // Weekend 9
            ['2026-09-12', 9,  'Sparks',        '09:00:00', '12:00:00', 10],
            ['2026-09-12', 9,  'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-09-13', 9,  '1st Grade',     '10:00:00', '12:30:00', 6],
            // Weekend 10
            ['2026-09-19', 10, 'Sparks',        '09:00:00', '12:00:00', 10],
            ['2026-09-19', 10, 'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-09-20', 10, '1st Grade',     '10:00:00', '12:30:00', 6],
            // Weekend 11
            ['2026-09-26', 11, 'Sparks',        '09:00:00', '12:00:00', 10],
            ['2026-09-26', 11, 'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-09-27', 11, '1st Grade',     '10:00:00', '12:30:00', 6],
            // Weekend 12
            ['2026-10-03', 12, 'Sparks',        '09:00:00', '12:00:00', 10],
            ['2026-10-03', 12, 'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-10-04', 12, '1st Grade',     '10:00:00', '12:30:00', 6],
            // Weekend 13
            ['2026-10-10', 13, 'Sparks',        '09:00:00', '12:00:00', 10],
            ['2026-10-10', 13, 'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-10-11', 13, '1st Grade',     '10:00:00', '12:30:00', 6],
            // Weekend 14
            ['2026-10-17', 14, 'Sparks',        '09:00:00', '12:00:00', 10],
            ['2026-10-17', 14, 'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-10-18', 14, '1st Grade',     '10:00:00', '12:30:00', 6],
            // Weekend 15 — Sparks not running, Kindergarten only
            ['2026-10-24', 15, 'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-10-25', 15, '1st Grade',     '10:00:00', '12:30:00', 6],
            // Weekend 16 — Sparks not running, Kindergarten only
            ['2026-10-31', 16, 'Kindergarten',  '12:30:00', '15:00:00', 12],
            ['2026-11-01', 16, '1st Grade',     '10:00:00', '12:30:00', 6],
        ];

        foreach ($days as [$date, $weekendNumber, $program, $start, $end, $spots]) {
            TrainingDay::updateOrCreate(
                ['date' => $date, 'program' => $program],
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
        // Remove fall days and restore schema
        TrainingDay::where('weekend_number', '>=', 9)->delete();

        Schema::table('training_days', function (Blueprint $table) {
            $table->dropUnique(['date', 'program']);
            $table->dropColumn('program');
            $table->unique('date');
        });
    }
};
