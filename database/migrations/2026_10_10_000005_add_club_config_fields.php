<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('timezone', 50)->default('America/New_York')->after('roster_notify_phones');
            $table->string('twilio_from', 20)->nullable()->after('timezone');
            $table->timestamp('roster_last_sent_at')->nullable()->after('twilio_from');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'twilio_from', 'roster_last_sent_at']);
        });
    }
};
