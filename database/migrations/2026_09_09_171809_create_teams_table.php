<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('program'); // sparks, kindergarten, 1st_grade
            $table->string('coach_name')->nullable();
            $table->string('coach_email')->nullable();
            $table->string('format')->nullable();       // e.g. "4 v 4"
            $table->string('location')->nullable();
            $table->string('session_times')->nullable();
            $table->string('rules_url')->nullable();
            $table->string('schedule_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
