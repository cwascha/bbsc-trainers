<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TeamController extends Controller
{
    public function index()
    {
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

    public function sync(): RedirectResponse
    {
        try {
            Artisan::call('rosters:sync');
            $output = Artisan::output();
            return back()->with('success', 'Roster sync complete. ' . trim($output));
        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
