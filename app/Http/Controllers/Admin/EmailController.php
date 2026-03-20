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

        $trainers = User::whereIn('id', $request->recipients)->get();

        foreach ($trainers as $trainer) {
            Mail::to($trainer->email, $trainer->name)
                ->send(new TrainerEmail($request->subject, $request->body, $trainer->name));
        }

        return back()->with('success', "Email sent to {$trainers->count()} trainer(s).");
    }
}
