<?php

namespace App\Console\Commands;

use App\Models\Club;
use App\Models\User;
use Illuminate\Console\Command;

class CreateSuperAdmin extends Command
{
    protected $signature = 'superadmin:create {email} {name} {password}';
    protected $description = 'Create or promote a user to superadmin';

    public function handle(): void
    {
        $club = Club::first();

        $user = User::withoutGlobalScopes()->updateOrCreate(
            ['email' => $this->argument('email')],
            [
                'club_id'            => $club->id,
                'name'               => $this->argument('name'),
                'role'               => 'superadmin',
                'password'           => bcrypt($this->argument('password')),
                'email_verified_at'  => now(),
            ]
        );

        $this->info("Done: {$user->email} is now superadmin.");
    }
}
