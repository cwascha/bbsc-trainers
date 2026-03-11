<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\NotificationLog;
use App\Models\TrainingDay;
use App\Services\SmsService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly SmsService $smsService) {}

    public function index()
    {
        $logs = NotificationLog::with(['user', 'trainingDay'])
            ->orderByDesc('sent_at')
            ->paginate(50);

        return view('admin.notifications.index', compact('logs'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'training_day_id' => 'required|exists:training_days,id',
        ]);

        $day = TrainingDay::findOrFail($request->training_day_id);

        $assigned = Availability::where('training_day_id', $day->id)
            ->whereIn('status', ['assigned', 'confirmed'])
            ->with('user')
            ->get();

        foreach ($assigned as $availability) {
            $this->smsService->sendAssignmentNotification($availability->user, $day);
        }

        return back()->with('success', "Sent {$assigned->count()} notification(s) for {$day->formattedDate}.");
    }
}
