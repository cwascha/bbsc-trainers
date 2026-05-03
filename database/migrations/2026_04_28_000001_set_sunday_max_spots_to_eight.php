<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reduce max spots for all Sundays (day-of-week 0 in SQLite strftime)
        DB::statement("UPDATE training_days SET max_spots = 8 WHERE strftime('%w', date) = '0'");
    }

    public function down(): void
    {
        DB::statement("UPDATE training_days SET max_spots = 12 WHERE strftime('%w', date) = '0'");
    }
};
