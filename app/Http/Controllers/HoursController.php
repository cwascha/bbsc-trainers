<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HoursController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $workedSessions = $user->availabilities()
            ->with('trainingDay')
            ->whereIn('status', ['assigned', 'confirmed'])
            ->whereHas('trainingDay', fn($q) => $q->where('date', '<', now()->toDateString()))
            ->get()
            ->sortByDesc('trainingDay.date');

        $totalHours = $workedSessions->count() * 7;

        return view('hours.index', compact('workedSessions', 'totalHours'));
    }
}
