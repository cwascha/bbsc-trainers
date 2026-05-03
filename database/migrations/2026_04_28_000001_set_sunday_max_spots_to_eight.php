<?php

use App\Models\TrainingDay;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Use Eloquent so Carbon handles day-of-week — no SQL date functions needed,
        // works on both SQLite (local) and MySQL (production).
        TrainingDay::all()
            ->filter(fn($d) => $d->date->dayOfWeek === 0) // 0 = Sunday in Carbon
            ->each(fn($d) => $d->update(['max_spots' => 8]));
    }

    public function down(): void
    {
        TrainingDay::all()
            ->filter(fn($d) => $d->date->dayOfWeek === 0)
            ->each(fn($d) => $d->update(['max_spots' => 12]));
    }
};
