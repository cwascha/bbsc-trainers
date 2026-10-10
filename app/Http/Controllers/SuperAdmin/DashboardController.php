<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $clubs = Club::withCount(['users', 'trainingDays'])
            ->with('users')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total_clubs'    => $clubs->count(),
            'active_clubs'   => $clubs->where('subscription_status', 'active')->count(),
            'trial_clubs'    => $clubs->where('subscription_status', 'trial')->count(),
            'total_trainers' => User::where('role', 'trainer')->count(),
        ];

        return view('superadmin.dashboard', compact('clubs', 'stats'));
    }

    public function impersonate(Club $club)
    {
        session(['impersonating_club_id' => $club->id]);
        return redirect('/')->with('success', "Now viewing as {$club->name}");
    }

    public function stopImpersonating()
    {
        session()->forget('impersonating_club_id');
        return redirect(route('superadmin.dashboard'));
    }

    public function updateStatus(Club $club, string $status)
    {
        abort_unless(in_array($status, ['active', 'trial', 'inactive', 'cancelled']), 422);
        $club->update(['subscription_status' => $status]);
        return back()->with('success', "Club status updated to {$status}.");
    }
}
