<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminInvitation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->orderBy('name')->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name'              => trim($request->name),
            'email'             => strtolower(trim($request->email)),
            'role'              => 'admin',
            'password'          => Str::random(32),
            'email_verified_at' => now(),
        ]);

        // Delete any existing token first to avoid duplicate key errors
        Password::deleteToken($user);
        $token = Password::createToken($user);

        Mail::to($user->email)->send(new AdminInvitation($user, $token));

        return back()->with('success', "Admin account created for {$user->name}. An invitation email has been sent to {$user->email}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own admin account.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "{$name} has been removed as an admin.");
    }
}
