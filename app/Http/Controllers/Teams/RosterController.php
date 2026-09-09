<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;

class RosterController extends Controller
{
    public function index()
    {
        $teams = Team::with('players')
            ->orderByRaw("FIELD(program, 'sparks', 'kindergarten', '1st_grade')")
            ->get();

        return view('teams.index', compact('teams'));
    }

    public function show(Team $team)
    {
        $team->load('players');
        return view('teams.show', compact('team'));
    }
}
