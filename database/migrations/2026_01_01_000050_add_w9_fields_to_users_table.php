<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('w9_path')->nullable()->after('phone');
            $table->timestamp('w9_uploaded_at')->nullable()->after('w9_path');
            $table->timestamp('w9_received_at')->nullable()->after('w9_uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['w9_path', 'w9_uploaded_at', 'w9_received_at']);
        });
    }
};
