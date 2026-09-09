<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    const PROGRAMS = [
        'sparks'       => 'Sparks',
        'kindergarten' => 'Kindergarten',
        '1st_grade'    => '1st Grade',
    ];

    // Maps display names / aliases to the program slug
    const PROGRAM_ALIASES = [
        'sparks'       => 'sparks',
        'kindergarten' => 'kindergarten',
        'kinder'       => 'kindergarten',
        'k'            => 'kindergarten',
        '1st grade'    => '1st_grade',
        '1st'          => '1st_grade',
        'first grade'  => '1st_grade',
        '1stgrade'     => '1st_grade',
    ];

    public function index()
    {
        foreach (self::PROGRAMS as $program => $name) {
            Team::firstOrCreate(['program' => $program], ['name' => $name]);
        }

        $teams = Team::with('players')
            ->orderByRaw("FIELD(program, 'sparks', 'kindergarten', '1st_grade')")
            ->get();

        return view('admin.teams.index', compact('teams'));
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'coach_name'    => 'nullable|string|max:255',
            'coach_email'   => 'nullable|email|max:255',
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

    // Single CSV for all teams — requires a "Team" column
    public function importAll(Request $request): RedirectResponse
    {
        $request->validate(['csv' => 'required|file|mimes:csv,txt|max:5120']);

        $handle  = fopen($request->file('csv')->getRealPath(), 'r');
        $headers = array_map('strtolower', array_map('trim', fgetcsv($handle)));

        $nameCol = $this->colIndex($headers, ['name', 'player name', 'player']);
        $teamCol = $this->colIndex($headers, ['team', 'program', 'group']);
        $roleCol = $this->colIndex($headers, ['role']);

        if ($nameCol === null) {
            fclose($handle);
            return back()->with('error', 'CSV must have a "Name" column.');
        }
        if ($teamCol === null) {
            fclose($handle);
            return back()->with('error', 'CSV must have a "Team" or "Program" column for a combined roster import.');
        }

        // Wipe all players before re-import
        Player::whereIn('team_id', Team::pluck('id'))->delete();
        Team::query()->update(['coach_name' => null]);

        $counts = [];

        while (($row = fgetcsv($handle)) !== false) {
            $name    = trim($row[$nameCol] ?? '');
            $teamRaw = strtolower(trim($row[$teamCol] ?? ''));
            if (! $name || ! $teamRaw) {
                continue;
            }

            $program = self::PROGRAM_ALIASES[$teamRaw] ?? null;
            if (! $program) {
                continue; // unknown team — skip
            }

            $team = Team::where('program', $program)->first();
            if (! $team) {
                continue;
            }

            $role = strtolower(trim($row[$roleCol] ?? ''));
            if (str_contains($role, 'coach') && ! $team->coach_name) {
                $team->update(['coach_name' => $name]);
                continue;
            }

            Player::create(['team_id' => $team->id, 'name' => $name]);
            $counts[$program] = ($counts[$program] ?? 0) + 1;
        }

        fclose($handle);

        $summary = collect($counts)
            ->map(fn($n, $p) => self::PROGRAMS[$p] . ": {$n}")
            ->join(', ');

        return back()->with('success', "Roster imported — {$summary}.");
    }

    private function colIndex(array $headers, array $candidates): ?int
    {
        foreach ($candidates as $candidate) {
            $i = array_search($candidate, $headers, true);
            if ($i !== false) {
                return $i;
            }
        }
        return null;
    }
}
