<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('teams')
            ->where('coach_name', 'Vivienne Agnew')
            ->update(['coach_name' => 'Casey Agnew']);
    }

    public function down(): void
    {
        DB::table('teams')
            ->where('coach_name', 'Casey Agnew')
            ->update(['coach_name' => 'Vivienne Agnew']);
    }
};
