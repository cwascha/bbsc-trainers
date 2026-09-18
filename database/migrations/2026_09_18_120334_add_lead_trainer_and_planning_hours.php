<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_lead_trainer')->default(false)->after('pay_rate');
        });

        Schema::table('payroll_hours_overrides', function (Blueprint $table) {
            $table->decimal('planning_hours', 6, 2)->default(0)->after('hours');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_lead_trainer');
        });

        Schema::table('payroll_hours_overrides', function (Blueprint $table) {
            $table->dropColumn('planning_hours');
        });
    }
};
