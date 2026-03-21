<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TrainerEmail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function index()
    {
        $trainers = User::where('role', 'trainer')->orderBy('name')->get();
        return view('admin.email.index', compact('trainers'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'body' => 'required|string|max:10000',
        ]);

        // Allow enough time to send many emails over SMTP
        ini_set('max_execution_time', 120);

        $trainers = User::whereIn('id', $request->recipients)->get();

        $sent   = 0;
        $failed = 0;

        foreach ($trainers as $trainer) {
            try {
                Mail::to($trainer->email, $trainer->name)
                    ->send(new TrainerEmail($request->subject, $request->body, $trainer->name));
                $sent++;
                // Stay under Resend's 5 requests/second SMTP rate limit
                usleep(250000); // 250ms = max 4 emails/second
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send email to {$trainer->email}: " . $e->getMessage());
                $failed++;
            }
        }

        if ($failed === 0) {
            return back()->with('success', "Email sent to {$sent} trainer(s).");
        }

        return back()->with('success', "Email sent to {$sent} trainer(s). {$failed} failed — check the application logs for details.");
    }
}
