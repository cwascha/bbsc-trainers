<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_days', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->tinyInteger('weekend_number');
            $table->tinyInteger('max_spots')->default(12);
            $table->time('session_start')->default('08:30:00');
            $table->time('session_end')->default('15:30:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_days');
    }
};
