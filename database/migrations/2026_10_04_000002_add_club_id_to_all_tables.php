<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'users',
        'training_days',
        'availabilities',
        'training_plans',
        'notification_logs',
        'documents',
        'payroll_hours_overrides',
        'payroll_payments',
        'recurring_services',
        'teams',
        'players',
    ];

    public function up(): void
    {
        // Insert BBSC as the founding club before adding foreign keys
        DB::table('clubs')->insertOrIgnore([
            'id'                  => 1,
            'name'                => 'BBSC',
            'slug'                => 'bbsc',
            'primary_color'       => '#111827',
            'accent_color'        => '#3b82f6',
            'subscription_status' => 'active',
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        foreach ($this->tables as $table) {
            if (! Schema::hasColumn($table, 'club_id')) {
                Schema::table($table, function (Blueprint $t) {
                    // Nullable so existing rows survive before the backfill
                    $t->unsignedBigInteger('club_id')->nullable()->after('id');
                });
            }

            // Backfill all existing rows to BBSC (club 1)
            DB::table($table)->whereNull('club_id')->update(['club_id' => 1]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'club_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('club_id');
                });
            }
        }
    }
};
