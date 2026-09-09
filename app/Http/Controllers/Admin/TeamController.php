<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    private const PROGRAMS = [
        'sparks'       => 'Sparks',
        'kindergarten' => 'Kindergarten',
        '1st_grade'    => '1st Grade',
    ];

    public function index()
    {
        // Ensure all three programs have team records
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

    public function import(Request $request, Team $team): RedirectResponse
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('csv')->getRealPath();
        $handle = fopen($path, 'r');

        $headers = array_map('strtolower', array_map('trim', fgetcsv($handle)));

        $nameCol   = $this->colIndex($headers, ['name', 'player name', 'player']);
        $roleCol   = $this->colIndex($headers, ['role']);
        $jerseyCol = $this->colIndex($headers, ['jersey', 'jersey #', 'jersey number', '#']);

        if ($nameCol === null) {
            fclose($handle);
            return back()->with('error', 'CSV must have a "Name" column.');
        }

        $team->players()->delete();
        $imported = 0;
        $coachSet = false;

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[$nameCol] ?? '');
            if (! $name) {
                continue;
            }

            $role = strtolower(trim($row[$roleCol] ?? ''));

            if (str_contains($role, 'coach') && ! $coachSet) {
                $team->update(['coach_name' => $name]);
                $coachSet = true;
                continue;
            }

            Player::create([
                'team_id'       => $team->id,
                'name'          => $name,
                'jersey_number' => trim($row[$jerseyCol] ?? ''),
            ]);
            $imported++;
        }

        fclose($handle);

        return back()->with('success', "{$team->name}: imported {$imported} players." . ($coachSet ? ' Coach updated.' : ''));
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
