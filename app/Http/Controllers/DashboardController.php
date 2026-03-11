<?php

namespace App\Http\Controllers;

use App\Models\TrainingDay;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $upcomingAssigned = $user->availabilities()
            ->with('trainingDay')
            ->whereIn('status', ['assigned', 'confirmed'])
            ->whereHas('trainingDay', fn($q) => $q->where('date', '>=', now()->toDateString()))
            ->orderBy('id')
            ->get()
            ->sortBy('trainingDay.date');

        $nextDays = TrainingDay::where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->take(4)
            ->get();

        $totalHours = $user->hoursWorked();
        $totalSessions = $user->availabilities()
            ->whereIn('status', ['assigned', 'confirmed'])
            ->whereHas('trainingDay', fn($q) => $q->where('date', '<', now()->toDateString()))
            ->count();

        return view('dashboard', compact('upcomingAssigned', 'nextDays', 'totalHours', 'totalSessions'));
    }
}
