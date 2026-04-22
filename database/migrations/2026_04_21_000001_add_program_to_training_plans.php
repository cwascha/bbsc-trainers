<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_plans', function (Blueprint $table) {
            // 'main' = K/1st grade, 'sparks' = pre-K (Saturday only)
            $table->string('program', 20)->default('main')->after('weekend_number');
        });
    }

    public function down(): void
    {
        Schema::table('training_plans', function (Blueprint $table) {
            $table->dropColumn('program');
        });
    }
};
