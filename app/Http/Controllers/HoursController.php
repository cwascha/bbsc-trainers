<?php

namespace App\Http\Controllers;

use App\Concerns\ResolvesPayPeriod;
use App\Models\PayrollHoursOverride;
use App\Models\TrainingDay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HoursController extends Controller
{
    use ResolvesPayPeriod;

    public function index(Request $request)
    {
        $user = $request->user();

        $workedSessions = $user->availabilities()
            ->with('trainingDay')
            ->whereIn('status', ['assigned', 'confirmed'])
            ->whereHas('trainingDay', fn($q) => $q->where('date', '<', now()->toDateString()))
            ->get()
            ->sortByDesc('trainingDay.date');

        $totalHours = $workedSessions->sum(fn($a) => $a->hoursWorked());

        // Lead trainer extras
        $currentPeriodStart = null;
        $currentPlanningHours = 0;

        if ($user->is_lead_trainer) {
            [$currentPeriodStart] = $this->currentPayPeriod();
            $override = PayrollHoursOverride::where('user_id', $user->id)
                ->where('period_start', $currentPeriodStart)
                ->first();
            $currentPlanningHours = $override ? (float) $override->planning_hours : 0;
        }

        return view('hours.index', compact('workedSessions', 'totalHours', 'currentPeriodStart', 'currentPlanningHours'));
    }

    public function updatePlanningHours(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->is_lead_trainer, 403);

        $request->validate([
            'period_start'   => 'required|date',
            'planning_hours' => 'required|numeric|min:0|max:999',
        ]);

        PayrollHoursOverride::updateOrCreate(
            ['user_id' => $user->id, 'period_start' => $request->period_start],
            ['planning_hours' => $request->planning_hours]
        );

        return back()->with('success', 'Planning hours saved.');
    }
}
