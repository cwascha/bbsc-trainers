<?php

use App\Models\Club;
use App\Models\Season;
use App\Models\TrainingDay;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->smallInteger('year');
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->timestamps();
        });

        Schema::table('training_days', function (Blueprint $table) {
            $table->foreignId('season_id')->nullable()->after('club_id')->constrained('seasons')->nullOnDelete();
        });

        // Backfill: group existing days into seasons by year + half-year
        Club::all()->each(function (Club $club) {
            app()->instance('currentClub', $club);

            $days = TrainingDay::whereNull('season_id')->get();
            if ($days->isEmpty()) return;

            // Group by year-half (Jan–Jun = Spring, Jul–Dec = Fall)
            $groups = $days->groupBy(function ($day) {
                $month = $day->date->month;
                $year  = $day->date->year;
                return $month <= 6 ? "Spring {$year}" : "Fall {$year}";
            });

            foreach ($groups as $label => $groupDays) {
                $year = (int) substr($label, -4);
                $status = $groupDays->first()->date->isPast() ? 'completed' : 'active';

                $season = Season::create([
                    'club_id' => $club->id,
                    'name'    => $label,
                    'year'    => $year,
                    'status'  => $status,
                ]);

                TrainingDay::whereIn('id', $groupDays->pluck('id'))->update(['season_id' => $season->id]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_days', function (Blueprint $table) {
            $table->dropForeign(['season_id']);
            $table->dropColumn('season_id');
        });

        Schema::dropIfExists('seasons');
    }
};
