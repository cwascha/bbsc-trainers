<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_day_id')->constrained()->cascadeOnDelete();
            $table->timestamp('signed_up_at');
            $table->enum('status', ['pending', 'assigned', 'confirmed', 'declined', 'cancelled'])->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'training_day_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
