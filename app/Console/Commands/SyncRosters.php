<?php

namespace App\Console\Commands;

use App\Models\Player;
use App\Models\Team;
use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;

class SyncRosters extends Command
{
    protected $signature   = 'rosters:sync {--program= : Only sync one program (sparks|kindergarten|1st_grade)}';
    protected $description = 'Sync player rosters from Google Sheets';

    public function handle(GoogleSheetsService $sheets): int
    {
        $spreadsheetId = config('google.spreadsheet_id');
        if (! $spreadsheetId) {
            $this->error('GOOGLE_SPREADSHEET_ID is not set.');
            return self::FAILURE;
        }

        $tabs    = config('google.tabs');
        $only    = $this->option('program');
        $synced  = 0;

        foreach ($tabs as $program => $tabName) {
            if ($only && $only !== $program) {
                continue;
            }

            $this->info("Syncing {$tabName}...");

            try {
                $rows = $sheets->getRows($spreadsheetId, $tabName);
            } catch (\Exception $e) {
                $this->error("  Failed: {$e->getMessage()}");
                continue;
            }

            // Find or create the team for this program
            $team = Team::firstOrCreate(
                ['program' => $program],
                ['name' => config("google.tabs.{$program}")]
            );

            // Wipe existing players and re-import fresh
            $team->players()->delete();

            foreach ($rows as $row) {
                $name = trim($row['name'] ?? $row['player name'] ?? $row['player'] ?? '');
                if (! $name) {
                    continue;
                }

                $role = strtolower(trim($row['role'] ?? ''));

                // If this row is the coach, store on the team (don't add as player)
                if (str_contains($role, 'coach')) {
                    $team->update(['coach_name' => $name]);
                    continue;
                }

                Player::create([
                    'team_id' => $team->id,
                    'name'    => $name,
                ]);
            }

            $count = $team->players()->count();
            $this->info("  Done — {$count} players.");
            $synced++;
        }

        $this->info("Roster sync complete ({$synced} program(s)).");
        return self::SUCCESS;
    }
}
