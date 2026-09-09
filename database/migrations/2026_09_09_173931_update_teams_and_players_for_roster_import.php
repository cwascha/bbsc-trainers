<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add group_name to teams (e.g. "Kindergarten Girls")
        Schema::table('teams', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('name');
            $table->dropColumn('coach_email'); // not displayed publicly
        });

        // Replace single name column with first_name + last_name on players
        Schema::table('players', function (Blueprint $table) {
            $table->string('first_name')->after('team_id');
            $table->string('last_name')->after('first_name');
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('group_name');
            $table->string('coach_email')->nullable();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('name')->after('team_id');
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
