<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite uses strftime('%w') where 0 = Sunday
        // MySQL uses DAYOFWEEK() where 1 = Sunday
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement("UPDATE training_days SET max_spots = 8 WHERE strftime('%w', date) = '0'");
        } else {
            DB::statement("UPDATE training_days SET max_spots = 8 WHERE DAYOFWEEK(date) = 1");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement("UPDATE training_days SET max_spots = 12 WHERE strftime('%w', date) = '0'");
        } else {
            DB::statement("UPDATE training_days SET max_spots = 12 WHERE DAYOFWEEK(date) = 1");
        }
    }
};
