<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();         // used in subdomain: bbsc.pitchside.app
            $table->string('primary_color', 7)->default('#1e3a5f');
            $table->string('accent_color', 7)->default('#3b82f6');
            $table->string('logo_path')->nullable();
            $table->string('stripe_customer_id')->nullable()->index();
            $table->string('subscription_status')->default('trial'); // trial|active|past_due|cancelled
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
