<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE availabilities MODIFY COLUMN status ENUM('pending','assigned','confirmed','declined','cancelled','no_show') DEFAULT 'pending'");
            DB::statement("ALTER TABLE availabilities ADD COLUMN IF NOT EXISTS no_showed_at TIMESTAMP NULL AFTER cancelled_at");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE availabilities SET status = 'cancelled' WHERE status = 'no_show'");
            DB::statement("ALTER TABLE availabilities MODIFY COLUMN status ENUM('pending','assigned','confirmed','declined','cancelled') DEFAULT 'pending'");
            DB::statement("ALTER TABLE availabilities DROP COLUMN IF EXISTS no_showed_at");
        }
    }
};
