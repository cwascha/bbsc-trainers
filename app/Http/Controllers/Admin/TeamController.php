<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeamController extends Controller
{
    // Tab names in the spreadsheet to import as player groups
    private const PLAYER_TABS = [
        'Kindergarten Girls',
        'Kindergarten Boys',
        '1st Grade Girls',
        '1st Grade Boys',
    ];

    // Program slugs for each group
    private const PROGRAM_SLUGS = [
        'Kindergarten Girls' => 'kindergarten_girls',
        'Kindergarten Boys'  => 'kindergarten_boys',
        '1st Grade Girls'    => '1st_grade_girls',
        '1st Grade Boys'     => '1st_grade_boys',
    ];

    public function index()
    {
        $teams = Team::with('players')
            ->orderByRaw("FIELD(program, 'kindergarten_girls','kindergarten_boys','1st_grade_girls','1st_grade_boys','sparks','kindergarten','1st_grade')")
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('admin.teams.index', compact('teams'));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'coach_name'    => 'nullable|string|max:255',
            'format'        => 'nullable|string|max:255',
            'location'      => 'nullable|string|max:255',
            'session_times' => 'nullable|string|max:255',
            'rules_url'     => 'nullable|url|max:500',
            'schedule_url'  => 'nullable|url|max:500',
            'notes'         => 'nullable|string|max:2000',
        ]);

        $team->update($validated);

        return back()->with('success', "{$team->name} updated.");
    }

    public function importAll(Request $request): RedirectResponse
    {
        $request->validate([
            'roster' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $path        = $request->file('roster')->getRealPath();
        $spreadsheet = IOFactory::load($path);

        // Wipe existing imported teams and players (keep manually-configured ones if needed)
        Player::query()->delete();
        Team::query()->delete();

        $teamMap   = []; // [group][teamName] => Team model
        $counts    = [];

        // ── Pass 1: parse player tabs ──────────────────────────────────────────
        foreach (self::PLAYER_TABS as $tabName) {
            $sheet = $spreadsheet->getSheetByName($tabName);
            if (! $sheet) {
                continue;
            }

            $program        = self::PROGRAM_SLUGS[$tabName];
            $currentTeam    = null;
            $inPlayerRows   = false;
            $rows           = $sheet->toArray(null, true, true, false);

            foreach ($rows as $row) {
                $col0 = trim((string) ($row[0] ?? ''));

                if (empty($col0)) {
                    continue;
                }

                // Detect team header: contains "(N players)"
                if (preg_match('/\((\d+)\s+players\)/i', $col0)) {
                    $teamName    = trim(preg_replace('/\s*\(\d+\s+players\).*/i', '', $col0));
                    $currentTeam = Team::create([
                        'name'       => $teamName,
                        'group_name' => $tabName,
                        'program'    => $program,
                    ]);
                    $teamMap[$tabName][$teamName] = $currentTeam;
                    $inPlayerRows                = false;
                    continue;
                }

                // Detect column header row
                if (strcasecmp($col0, 'First Name') === 0) {
                    $inPlayerRows = true;
                    continue;
                }

                if ($currentTeam && $inPlayerRows) {
                    $firstName = trim((string) ($row[0] ?? ''));
                    $lastName  = trim((string) ($row[1] ?? ''));

                    if (! $firstName && ! $lastName) {
                        continue;
                    }

                    Player::create([
                        'team_id'    => $currentTeam->id,
                        'first_name' => $firstName,
                        'last_name'  => $lastName,
                    ]);
                    $counts[$tabName] = ($counts[$tabName] ?? 0) + 1;
                }
            }
        }

        // ── Pass 2: parse Coaches tab to assign coaches to teams ────────────────
        $coachSheet = $spreadsheet->getSheetByName('Coaches');
        if ($coachSheet) {
            $rows    = $coachSheet->toArray(null, true, true, false);
            $inData  = false;

            foreach ($rows as $row) {
                $col0 = trim((string) ($row[0] ?? ''));

                // Header row: Group, Team, First Name, Last Name, ...
                if (strcasecmp($col0, 'Group') === 0) {
                    $inData = true;
                    continue;
                }

                if (! $inData || ! $col0) {
                    continue;
                }

                $group     = trim((string) ($row[0] ?? ''));
                $teamName  = trim((string) ($row[1] ?? ''));
                $firstName = trim((string) ($row[2] ?? ''));
                $lastName  = trim((string) ($row[3] ?? ''));

                $team = $teamMap[$group][$teamName] ?? null;
                if ($team && $firstName && ! $team->coach_name) {
                    $team->update(['coach_name' => "{$firstName} {$lastName}"]);
                }
            }
        }

        $summary = collect($counts)
            ->map(fn ($n, $g) => "{$g}: {$n} players")
            ->join(' | ');

        $teamCount = Team::count();

        return back()->with('success', "Imported {$teamCount} teams. {$summary}");
    }
}
