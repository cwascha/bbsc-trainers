<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendTrainerEmail;
use App\Models\Availability;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function index()
    {
        $trainers = User::where('role', 'trainer')->orderBy('name')->get();

        // Build a map of weekend_number => [user_ids] for assigned/confirmed trainers
        $weekendTrainers = Availability::whereIn('status', ['assigned', 'confirmed'])
            ->with('trainingDay')
            ->get()
            ->groupBy(fn($a) => $a->trainingDay->weekend_number)
            ->map(fn($avs) => $avs->pluck('user_id')->unique()->values());

        return view('admin.email.index', compact('trainers', 'weekendTrainers'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'recipients'   => 'required|array|min:1',
            'recipients.*' => 'exists:users,id',
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string|max:10000',
        ]);

        $trainers = User::whereIn('id', $request->recipients)->get();

        // Stagger dispatches by 1 second each to stay within Resend's rate limit
        foreach ($trainers->values() as $i => $trainer) {
            SendTrainerEmail::dispatch(
                $trainer->email,
                $trainer->name,
                $request->subject,
                $request->body,
            )->delay(now()->addSeconds($i));
        }

        return back()->with('success', "Email queued for {$trainers->count()} trainer(s). They will be delivered shortly.");
    }
}
