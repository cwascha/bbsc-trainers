<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_days', function (Blueprint $table) {
            $table->dropUnique(['date', 'program']);
            $table->unique(['date', 'program', 'club_id']);
        });
    }

    public function down(): void
    {
        Schema::table('training_days', function (Blueprint $table) {
            $table->dropUnique(['date', 'program', 'club_id']);
            $table->unique(['date', 'program']);
        });
    }
};
