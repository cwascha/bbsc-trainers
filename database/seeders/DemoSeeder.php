<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\Club;
use App\Models\Season;
use App\Models\TrainingDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Wipe existing demo club data
        $demo = Club::where('slug', 'demo')->first();
        if ($demo) {
            Availability::where('club_id', $demo->id)->delete();
            TrainingDay::where('club_id', $demo->id)->delete();
            Season::where('club_id', $demo->id)->delete();
            User::where('club_id', $demo->id)->delete();
            $demo->delete();
        }

        // Create demo club
        $club = Club::create([
            'name'                => 'Riverside FC',
            'slug'                => 'demo',
            'is_demo'             => true,
            'subscription_status' => 'active',
            'primary_color'       => '#1d4ed8',
            'accent_color'        => '#facc15',
            'contact_email'       => 'admin@riversidefc.demo',
            'timezone'            => 'America/New_York',
            'roster_notify_phones' => '5550001111',
            'teams_field_name'    => 'Riverside Community Park',
            'teams_field_map_query' => 'Riverside+Community+Park,+Ann+Arbor,+MI',
            'teams_head_trainer'  => 'Coach Demo',
        ]);

        // Admin user
        User::create([
            'club_id'           => $club->id,
            'name'              => 'Demo Admin',
            'email'             => 'admin@demo.trainersync.co',
            'email_verified_at' => now(),
            'role'              => 'admin',
            'phone'             => '5550000000',
            'password'          => Hash::make('demo1234'),
        ]);

        // Trainers with varied statuses
        $trainers = [
            ['name' => 'Jordan Reyes',   'email' => 'jordan@demo.ts',   'phone' => '5550001001', 'pay_rate' => 22, 'is_lead' => true],
            ['name' => 'Aaliyah Grant',  'email' => 'aaliyah@demo.ts',  'phone' => '5550001002', 'pay_rate' => 20, 'is_lead' => false],
            ['name' => 'Marcus Webb',    'email' => 'marcus@demo.ts',   'phone' => '5550001003', 'pay_rate' => 20, 'is_lead' => false],
            ['name' => 'Priya Patel',    'email' => 'priya@demo.ts',    'phone' => '5550001004', 'pay_rate' => 18, 'is_lead' => false],
            ['name' => 'Dante Morales',  'email' => 'dante@demo.ts',    'phone' => '5550001005', 'pay_rate' => 18, 'is_lead' => false],
            ['name' => 'Sophie Chen',    'email' => 'sophie@demo.ts',   'phone' => '5550001006', 'pay_rate' => 18, 'is_lead' => false],
            ['name' => 'Elijah Brooks',  'email' => 'elijah@demo.ts',   'phone' => '5550001007', 'pay_rate' => 16, 'is_lead' => false],
            ['name' => 'Nadia Osei',     'email' => 'nadia@demo.ts',    'phone' => '5550001008', 'pay_rate' => 16, 'is_lead' => false],
            ['name' => 'Tyler Nguyen',   'email' => 'tyler@demo.ts',    'phone' => '5550001009', 'pay_rate' => 16, 'is_lead' => false],
            ['name' => 'Camille Dubois', 'email' => 'camille@demo.ts',  'phone' => '5550001010', 'pay_rate' => 16, 'is_lead' => false],
            ['name' => 'Kwame Asante',   'email' => 'kwame@demo.ts',    'phone' => '5550001011', 'pay_rate' => 16, 'is_lead' => false],
            ['name' => 'Lily Park',      'email' => 'lily@demo.ts',     'phone' => '5550001012', 'pay_rate' => 16, 'is_lead' => false],
        ];

        $trainerModels = [];
        foreach ($trainers as $t) {
            $trainerModels[] = User::create([
                'club_id'           => $club->id,
                'name'              => $t['name'],
                'email'             => $t['email'],
                'email_verified_at' => now(),
                'role'              => 'trainer',
                'phone'             => $t['phone'],
                'pay_rate'          => $t['pay_rate'],
                'is_lead_trainer'   => $t['is_lead'],
                'password'          => Hash::make('demo1234'),
            ]);
        }

        // Season
        $season = Season::create([
            'club_id' => $club->id,
            'name'    => 'Fall 2026',
            'year'    => 2026,
            'status'  => 'active',
        ]);

        // Training days: 4 past weekends (with assignments), 4 future weekends
        $weekends = [
            // Past (for payroll demo)
            ['2026-09-06', '2026-09-07', 1],
            ['2026-09-13', '2026-09-14', 2],
            ['2026-09-20', '2026-09-21', 3],
            ['2026-09-27', '2026-09-28', 4],
            // Future (for scheduling demo)
            ['2026-10-18', '2026-10-19', 5],
            ['2026-10-25', '2026-10-26', 6],
            ['2026-11-01', '2026-11-02', 7],
            ['2026-11-08', '2026-11-09', 8],
        ];

        $programs = ['Sparks', 'Lightning', 'Thunder', 'Elite'];

        $dayModels = [];
        foreach ($weekends as [$sat, $sun, $wknd]) {
            $prog = $programs[($wknd - 1) % count($programs)];
            $dayModels[] = TrainingDay::create([
                'club_id'        => $club->id,
                'season_id'      => $season->id,
                'date'           => $sat,
                'program'        => $prog,
                'weekend_number' => $wknd,
                'max_spots'      => 8,
                'session_start'  => '09:00:00',
                'session_end'    => '14:00:00',
            ]);
            $dayModels[] = TrainingDay::create([
                'club_id'        => $club->id,
                'season_id'      => $season->id,
                'date'           => $sun,
                'program'        => $prog,
                'weekend_number' => $wknd,
                'max_spots'      => 6,
                'session_start'  => '09:00:00',
                'session_end'    => '12:00:00',
            ]);
        }

        // Seed availabilities for past days (confirmed/assigned) and upcoming (pending)
        $pastDays   = array_slice($dayModels, 0, 8);   // first 4 weekends = 8 days
        $futureDays = array_slice($dayModels, 8, 4);   // next 2 weekends = 4 days (upcoming)

        foreach ($pastDays as $day) {
            // Pick 6-8 trainers per past day, mark confirmed with some no-shows
            $picked = collect($trainerModels)->shuffle()->take(rand(6, 8));
            foreach ($picked as $i => $trainer) {
                Availability::create([
                    'club_id'         => $club->id,
                    'user_id'         => $trainer->id,
                    'training_day_id' => $day->id,
                    'signed_up_at'    => Carbon::parse($day->date)->subDays(10),
                    'status'          => 'confirmed',
                    'confirmed_at'    => Carbon::parse($day->date)->subDays(4),
                ]);
            }
        }

        foreach ($futureDays as $day) {
            // Some trainers have signed up, not yet assigned
            $picked = collect($trainerModels)->shuffle()->take(rand(4, 7));
            foreach ($picked as $trainer) {
                Availability::create([
                    'club_id'         => $club->id,
                    'user_id'         => $trainer->id,
                    'training_day_id' => $day->id,
                    'signed_up_at'    => now()->subDays(rand(1, 5)),
                    'status'          => 'pending',
                ]);
            }
        }
    }
}
