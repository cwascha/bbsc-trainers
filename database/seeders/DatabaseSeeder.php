<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure BBSC exists as club 1 before anything else
        $club = Club::firstOrCreate(
            ['slug' => 'bbsc'],
            [
                'name'                => 'BBSC',
                'primary_color'       => '#1e3a5f',
                'accent_color'        => '#3b82f6',
                'subscription_status' => 'active',
            ]
        );

        // Make it available to seeders that use the BelongsToClub trait
        app()->instance('currentClub', $club);

        // Seed all training days
        $this->call(TrainingDaySeeder::class);

        // Create default admin account
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@bbsc.com')],
            [
                'club_id'           => $club->id,
                'name'              => 'BBSC Admin',
                'phone'             => null,
                'role'              => 'admin',
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Seeded training days and admin account (admin@bbsc.com / password).');
        $this->command->warn('Remember to change the admin password!');
    }
}
