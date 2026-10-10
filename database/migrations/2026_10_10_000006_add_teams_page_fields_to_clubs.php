<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('teams_field_name', 200)->nullable()->after('roster_last_sent_at');
            $table->string('teams_field_map_query', 300)->nullable()->after('teams_field_name');
            $table->string('teams_rules_url', 500)->nullable()->after('teams_field_map_query');
            $table->string('teams_schedule_url', 500)->nullable()->after('teams_rules_url');
            $table->text('teams_jersey_info')->nullable()->after('teams_schedule_url');
            $table->string('teams_head_trainer', 100)->nullable()->after('teams_jersey_info');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn([
                'teams_field_name',
                'teams_field_map_query',
                'teams_rules_url',
                'teams_schedule_url',
                'teams_jersey_info',
                'teams_head_trainer',
            ]);
        });
    }
};
