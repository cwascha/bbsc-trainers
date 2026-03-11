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
            $availability->update(['status' => 'confirmed', 'confirmed_at' => now()]);
            return $this->twimlResponse("Thanks, {$user->name}! Your session on {$day->formattedDate} is confirmed. See you at 8:30 AM!");
        }

        if (in_array($body, ['NO', 'N', 'CANCEL', 'NO!', '2'])) {
            $availability->update(['status' => 'declined', 'cancelled_at' => now()]);
            $this->assignmentService->reassignDay($day);
            return $this->twimlResponse("Understood, {$user->name}. Your session on {$day->formattedDate} has been cancelled. We've notified the next trainer.");
        }

        return $this->twimlResponse("Hi {$user->name}! Reply YES to confirm your session on {$day->formattedDate} or NO to cancel.");
    }

    private function twimlResponse(string $message): Response
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><Response><Message>{$message}</Message></Response>";
        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }
}
