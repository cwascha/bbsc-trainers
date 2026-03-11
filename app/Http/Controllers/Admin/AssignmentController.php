<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\TrainingDay;
use App\Services\AssignmentService;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentService $assignmentService) {}

    public function index()
    {
        $days = TrainingDay::where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->with(['availabilities.user'])
            ->get();

        return view('admin.sessions.index', compact('days'));
    }

    public function run(Request $request)
    {
        $request->validate([
            'training_day_id' => 'nullable|exists:training_days,id',
        ]);

        if ($request->training_day_id) {
            $day = TrainingDay::findOrFail($request->training_day_id);
            $assigned = $this->assignmentService->assignDay($day);
            $count = $assigned->count();
            return back()->with('success', "Assigned {$count} trainer(s) to {$day->formattedDate}.");
        }

        $this->assignmentService->assignUpcomingWeekend();
        return back()->with('success', 'Assignment run for the upcoming weekend.');
    }
}
