<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\TrainingDay;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTrainers = User::where('role', 'trainer')->count();
        $w9Received    = User::where('role', 'trainer')->whereNotNull('w9_received_at')->count();
        $w9Missing     = User::where('role', 'trainer')->whereNull('w9_path')->whereNull('w9_received_at')->count();

        $upcomingDays = TrainingDay::where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->take(6)
            ->withCount(['availabilities as assigned_count' => fn($q) => $q->whereIn('status', ['assigned', 'confirmed'])])
            ->withCount(['availabilities as pending_count' => fn($q) => $q->where('status', 'pending')])
            ->get();

        $recentSignups = Availability::with(['user', 'trainingDay'])
            ->orderByDesc('signed_up_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('totalTrainers', 'w9Received', 'w9Missing', 'upcomingDays', 'recentSignups'));
    }
}
