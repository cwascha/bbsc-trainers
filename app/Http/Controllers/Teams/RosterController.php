<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use App\Models\Team;

class RosterController extends Controller
{
    public function index()
    {
        $groups = Team::with('players')
            ->orderByRaw("FIELD(program, 'kindergarten_girls','kindergarten_boys','1st_grade_girls','1st_grade_boys')")
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('teams.index', compact('groups'));
    }

    public function show(Team $team)
    {
        $team->load('players');
        return view('teams.show', compact('team'));
    }
}
