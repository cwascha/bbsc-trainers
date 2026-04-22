<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_hours_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->decimal('hours', 6, 2);
            $table->timestamps();

            $table->unique(['user_id', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_hours_overrides');
    }
};
