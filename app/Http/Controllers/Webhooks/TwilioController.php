<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\TrainingDay;
use App\Models\User;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TwilioController extends Controller
{
    public function __construct(private readonly AssignmentService $assignmentService) {}

    public function handle(Request $request)
    {
        $from    = $request->input('From', '');
        $body    = strtoupper(trim($request->input('Body', '')));

        // Normalize phone for lookup
        $digits = preg_replace('/\D/', '', $from);
        $phone10 = substr($digits, -10);

        $user = User::where('role', 'trainer')
            ->get()
            ->first(function ($u) use ($phone10) {
                return substr(preg_replace('/\D/', '', $u->phone ?? ''), -10) === $phone10;
            });

        if (! $user) {
            return $this->twimlResponse('We could not find your account. Please contact your administrator.');
        }

        // Find the nearest upcoming assigned availability
        $availability = Availability::where('user_id', $user->id)
            ->whereIn('status', ['assigned', 'confirmed'])
            ->whereHas('trainingDay', fn($q) => $q->where('date', '>=', now()->toDateString()))
            ->with('trainingDay')
            ->get()
            ->sortBy('trainingDay.date')
            ->first();

        if (! $availability) {
            return $this->twimlResponse('You have no upcoming scheduled sessions.');
        }

        $day = $availability->trainingDay;

        if (in_array($body, ['YES', 'Y', 'CONFIRM', 'YES!', '1'])) {
            // Confirm all assigned sessions for the same weekend in one reply
            $weekendAssigned = Availability::where('user_id', $user->id)
                ->where('status', 'assigned')
                ->whereHas('trainingDay', fn($q) => $q
                    ->where('weekend_number', $day->weekend_number)
                    ->where('date', '>=', now()->toDateString())
                )
                ->with('trainingDay')
                ->get();

            foreach ($weekendAssigned as $av) {
                $av->update(['status' => 'confirmed', 'confirmed_at' => now()]);
            }

            // Also confirm the current availability if it wasn't already in the list (e.g. already confirmed)
            if ($availability->status !== 'confirmed') {
                $availability->update(['status' => 'confirmed', 'confirmed_at' => now()]);
            }

            $dates = $weekendAssigned->map(fn($av) => $av->trainingDay->formattedDate)->unique()->values();
            $dateStr = $dates->count() > 1
                ? $dates->slice(0, -1)->join(', ') . ' and ' . $dates->last()
                : ($dates->first() ?? $day->formattedDate);

            return $this->twimlResponse("Thanks, {$user->name}! Your session(s) on {$dateStr} are confirmed. See you there!");
        }

        if (in_array($body, ['NO', 'N', 'CANCEL', 'NO!', '2'])) {
            $availability->update(['status' => 'declined', 'cancelled_at' => now()]);
            $this->assignmentService->reassignDay($day);
            return $this->twimlResponse("Understood, {$user->name}. Your session on {$day->formattedDate} has been cancelled. We've notified the next trainer.");
        }

        $weekendBoth = Availability::where('user_id', $user->id)
            ->where('status', 'assigned')
            ->whereHas('trainingDay', fn($q) => $q->where('weekend_number', $day->weekend_number))
            ->count() > 1;

        $prompt = $weekendBoth
            ? "Hi {$user->name}! You're assigned for both days of Weekend {$day->weekend_number}. Reply YES to confirm both days or NO to cancel {$day->formattedDate}."
            : "Hi {$user->name}! Reply YES to confirm your session on {$day->formattedDate} or NO to cancel.";

        return $this->twimlResponse($prompt);
    }

    private function twimlResponse(string $message): Response
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><Response><Message>{$message}</Message></Response>";
        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }
}
