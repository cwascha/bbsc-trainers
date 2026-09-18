<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 17.50 → 20 first so those trainers don't accidentally get bumped twice
        DB::table('users')->where('role', 'trainer')->where('pay_rate', 17.50)->update(['pay_rate' => 20.00]);
        DB::table('users')->where('role', 'trainer')->where('pay_rate', 15.00)->update(['pay_rate' => 17.50]);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'trainer')->where('pay_rate', 17.50)->update(['pay_rate' => 15.00]);
        // Note: cannot reliably distinguish which 20.00 trainers were bumped from 17.50 vs already at 20
    }
};
