<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendBulkSms;
use App\Models\TrainingDay;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    public function __construct(private readonly SmsService $smsService) {}

    public function index()
    {
        $trainers = User::where('role', 'trainer')->orderBy('name')->get();
        return view('admin.sms.index', compact('trainers'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:users,id',
            'message'    => 'required|string|max:1600',
        ]);

        $count = count($request->recipients);
        SendBulkSms::dispatch($request->recipients, $request->message, currentClub()->id);

        return back()->with('success', "Queued SMS to {$count} trainer(s). Messages will arrive within a few minutes.");
    }

    public function sendRoster(): RedirectResponse
    {
        $exitCode = \Artisan::call('roster:notify');

        if ($exitCode === 0) {
            return back()->with('success', 'Weekend roster SMS sent successfully.');
        }

        return back()->with('error', 'Roster SMS failed — check that ROSTER_NOTIFY_PHONES and Twilio are configured. See the application logs for details.');
    }

    public function sendToDay(Request $request, TrainingDay $trainingDay): RedirectResponse
    {
        $request->validate(['message' => 'required|string|max:1600']);

        $ids = $trainingDay->availabilities()
            ->whereIn('status', ['assigned', 'confirmed'])
            ->with('user')
            ->get()
            ->pluck('user.id')
            ->filter()
            ->values()
            ->all();

        SendBulkSms::dispatch($ids, $request->message, currentClub()->id);

        $msg = "Queued SMS to " . count($ids) . " trainer(s) assigned to {$trainingDay->formattedDate}. Messages will arrive within a few minutes.";

        return back()->with('success', $msg);
    }
}
