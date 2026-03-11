<?php

namespace App\Http\Controllers;

use App\Models\TrainingDay;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $days = TrainingDay::orderBy('date')->get();

        // Map: training_day_id => ['status' => ..., 'id' => availability_id]
        $myAvailabilities = $user->availabilities()
            ->get(['id', 'training_day_id', 'status'])
            ->keyBy('training_day_id')
            ->map(fn($a) => ['status' => $a->status, 'id' => $a->id])
            ->toArray();

        $weekends = $days->groupBy('weekend_number');

        return view('schedule.index', compact('weekends', 'myAvailabilities'));
    }
}
