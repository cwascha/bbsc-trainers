<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\TrainingDay;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function show(int $weekend, Request $request)
    {
        $days = TrainingDay::where('weekend_number', $weekend)
            ->with([
                'availabilities' => fn($q) => $q
                    ->whereIn('status', ['assigned', 'confirmed', 'no_show'])
                    ->with('user:id,name'),
            ])
            ->orderBy('date')
            ->orderBy('session_start')
            ->get();

        abort_if($days->isEmpty(), 404);

        return view('attendance.show', compact('days', 'weekend'));
    }

    public function toggle(int $weekend, Availability $availability, Request $request)
    {
        if ($availability->status === 'no_show') {
            $availability->update(['status' => 'confirmed', 'no_showed_at' => null]);
        } else {
            $availability->update(['status' => 'no_show', 'no_showed_at' => now()]);
        }

        return redirect()->route('attendance.show', ['weekend' => $weekend])
            ->with('message', $availability->status === 'no_show'
                ? 'Marked as no-show.'
                : 'Marked as attended.');
    }
}
