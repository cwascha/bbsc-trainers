<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $trainers = User::whereIn('id', $request->recipients)->get();

        $sent   = 0;
        $failed = 0;
        $noPhone = 0;

        foreach ($trainers as $trainer) {
            if (! $trainer->phone) {
                $noPhone++;
                continue;
            }
            try {
                $this->smsService->sendCustom($trainer, $request->message);
                $sent++;
                usleep(200000); // stay under Twilio rate limits
            } catch (\Exception $e) {
                Log::error("Failed to send SMS to {$trainer->phone}: " . $e->getMessage());
                $failed++;
            }
        }

        $msg = "SMS sent to {$sent} trainer(s).";
        if ($noPhone)  $msg .= " {$noPhone} skipped (no phone on file).";
        if ($failed)   $msg .= " {$failed} failed — check logs.";

        return back()->with('success', $msg);
    }
}
