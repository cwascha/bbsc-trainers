<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\TrainingDay;
use App\Models\User;
use App\Services\AssignmentService;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(
        private readonly AssignmentService $assignmentService,
        private readonly SmsService $smsService,
    ) {}

    public function index()
    {
        $days = TrainingDay::where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->with(['availabilities.user'])
            ->get();

        $trainers = User::where('role', 'trainer')->orderBy('name')->get(['id', 'name']);

        return view('admin.sessions.index', compact('days', 'trainers'));
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

    public function addTrainer(Request $request, TrainingDay $trainingDay): RedirectResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $trainer = User::findOrFail($request->user_id);
        $direct  = $request->input('direct') === '1';

        if ($direct) {
            // Directly assign and send SMS notification
            $availability = Availability::updateOrCreate(
                ['user_id' => $trainer->id, 'training_day_id' => $trainingDay->id],
                ['status' => 'assigned', 'signed_up_at' => now()]
            );
            $this->smsService->sendAssignmentNotification($trainer, $trainingDay);
            return back()->with('success', "{$trainer->name} has been directly assigned to {$trainingDay->formattedDate} and notified by SMS.");
        }

        // Add to pending so they go through the normal assignment flow
        Availability::updateOrCreate(
            ['user_id' => $trainer->id, 'training_day_id' => $trainingDay->id],
            ['status' => 'pending', 'signed_up_at' => now()]
        );

        return back()->with('success', "{$trainer->name} added to pending for {$trainingDay->formattedDate}.");
    }

    public function assignSelected(Request $request, TrainingDay $trainingDay): RedirectResponse
    {
        $request->validate([
            'availability_ids'   => 'required|array|min:1',
            'availability_ids.*' => 'exists:availabilities,id',
        ]);

        $count = 0;
        foreach ($request->availability_ids as $avId) {
            $availability = Availability::with('user')
                ->where('id', $avId)
                ->where('training_day_id', $trainingDay->id)
                ->where('status', 'pending')
                ->first();

            if (! $availability) continue;

            $availability->update(['status' => 'assigned']);
            $this->smsService->sendAssignmentNotification($availability->user, $trainingDay);
            $count++;
        }

        return back()->with('success', "Assigned {$count} trainer(s) to {$trainingDay->formattedDate} and sent SMS notifications.");
    }

    public function removeTrainer(Availability $availability): RedirectResponse
    {
        $wasAssigned = in_array($availability->status, ['assigned', 'confirmed']);
        $day         = $availability->trainingDay;
        $name        = $availability->user->name;

        $availability->delete();

        // If they were assigned, try to fill the spot from pending sign-ups
        if ($wasAssigned) {
            $this->assignmentService->assignDay($day);
        }

        return back()->with('success', "{$name} has been removed from {$day->formattedDate}.");
    }
}
