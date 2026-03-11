<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed all training days
        $this->call(TrainingDaySeeder::class);

        // Create default admin account
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@bbsc.com')],
            [
                'name'     => 'BBSC Admin',
                'phone'    => null,
                'role'     => 'admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );

        $this->command->info('Seeded training days and admin account (admin@bbsc.com / password).');
        $this->command->warn('Remember to change the admin password!');
    }
}
