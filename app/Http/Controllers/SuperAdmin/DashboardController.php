<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

    public function destroy(Club $club)
    {
        $name = $club->name;

        DB::transaction(function () use ($club) {
            $dayIds  = $club->trainingDays()->pluck('id');
            $teamIds = DB::table('teams')->where('club_id', $club->id)->pluck('id');

            DB::table('players')->whereIn('team_id', $teamIds)->delete();
            DB::table('teams')->where('club_id', $club->id)->delete();
            DB::table('payroll_payments')->where('club_id', $club->id)->delete();
            DB::table('payroll_hours_overrides')->where('club_id', $club->id)->delete();
            DB::table('recurring_services')->where('club_id', $club->id)->delete();
            DB::table('notification_logs')->where('club_id', $club->id)->delete();
            DB::table('availabilities')->whereIn('training_day_id', $dayIds)->delete();
            DB::table('training_plans')->where('club_id', $club->id)->delete();
            DB::table('training_days')->where('club_id', $club->id)->delete();
            DB::table('documents')->where('club_id', $club->id)->delete();
            DB::table('users')->where('club_id', $club->id)->delete();
            $club->delete(); // seasons cascade via DB constraint
        });

        return redirect(route('superadmin.dashboard'))->with('success', "Club \"{$name}\" permanently deleted.");
    }
}
