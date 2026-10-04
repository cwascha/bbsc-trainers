<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clubs')->where('slug', 'bbsc')
            ->update(['primary_color' => '#111827']);
    }

    public function down(): void
    {
        DB::table('clubs')->where('slug', 'bbsc')
            ->update(['primary_color' => '#1e3a5f']);
    }
};
