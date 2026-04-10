<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\TrainingDay;
use App\Services\AssignmentService;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(private readonly AssignmentService $assignmentService) {}

    public function store(Request $request)
    {
        $request->validate([
            'training_day_id' => 'required|exists:training_days,id',
        ]);

        $day = TrainingDay::findOrFail($request->training_day_id);

        if ($day->isPast()) {
            return back()->with('error', 'Cannot sign up for a past session.');
        }

        // Use updateOrCreate so re-signing up after cancellation resets the status to pending
        Availability::updateOrCreate(
            [
                'user_id'         => $request->user()->id,
                'training_day_id' => $day->id,
            ],
            [
                'signed_up_at' => now(),
                'status'       => 'pending',
                'cancelled_at' => null,
            ]
        );

        return back()->with('success', "You've signed up for {$day->formattedDate}!");
    }

    public function destroy(Request $request, Availability $availability)
    {
        if ($availability->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($availability->trainingDay->isPast()) {
            return back()->with('error', 'Cannot cancel a past session.');
        }

        $wasAssigned = $availability->isAssigned();
        $day = $availability->trainingDay;

        $availability->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        if ($wasAssigned) {
            $this->assignmentService->reassignDay($day);
        }

        return back()->with('success', 'Your availability has been cancelled.');
    }
}
