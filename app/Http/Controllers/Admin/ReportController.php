<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\ResolvesPayPeriod;
use App\Http\Controllers\Controller;
use App\Models\PayrollHoursOverride;
use App\Models\PayrollPayment;
use App\Models\RecurringService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ResolvesPayPeriod;

    public function index(Request $request)
    {
        [$defaultStart, $defaultEnd] = $this->currentPayPeriod();

        $startDate = $request->input('start_date', $defaultStart);
        $endDate   = $request->input('end_date', $defaultEnd);

        $trainers = $this->getReport($startDate, $endDate);

        // Trainers not yet on this payroll (no sessions + no override)
        $addableTrainers = $trainers->filter(fn($t) => $t->sessions_count === 0 && ! $t->hours_override)
            ->sortBy('name')
            ->values();

        [$prevStart, $prevEnd] = $this->previousPayPeriod($startDate);
        [$nextStart, $nextEnd] = $this->nextPayPeriod($startDate);

        return view('admin.reports.index', compact(
            'trainers', 'addableTrainers', 'startDate', 'endDate', 'prevStart', 'prevEnd', 'nextStart', 'nextEnd'
        ));
    }

    public function updateHours(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'period_start'   => 'required|date',
            'hours'          => 'required|numeric|min:0|max:999',
            'planning_hours' => 'sometimes|numeric|min:0|max:999',
        ]);

        PayrollHoursOverride::updateOrCreate(
            ['user_id' => $user->id, 'period_start' => $request->period_start],
            array_filter([
                'hours'          => $request->hours,
                'planning_hours' => $request->input('planning_hours', 0),
            ], fn($v) => $v !== null)
        );

        return back()->with('success', "Hours updated for {$user->name}.");
    }

    public function updatePlanningHours(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'period_start'   => 'required|date',
            'planning_hours' => 'required|numeric|min:0|max:999',
        ]);

        PayrollHoursOverride::updateOrCreate(
            ['user_id' => $user->id, 'period_start' => $request->period_start],
            ['planning_hours' => $request->planning_hours]
        );

        return back()->with('success', "Planning hours updated for {$user->name}.");
    }

    public function clearHours(Request $request, User $user): RedirectResponse
    {
        $request->validate(['period_start' => 'required|date']);

        PayrollHoursOverride::where('user_id', $user->id)
            ->where('period_start', $request->period_start)
            ->delete();

        return back()->with('success', "Hours reset to calculated value for {$user->name}.");
    }

    public function addManual(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'period_start' => 'required|date',
            'hours'        => 'required|numeric|min:0|max:999',
        ]);

        $user = User::findOrFail($request->user_id);

        PayrollHoursOverride::updateOrCreate(
            ['user_id' => $user->id, 'period_start' => $request->period_start],
            ['hours'   => $request->hours]
        );

        return back()->with('success', "{$user->name} added to payroll with {$request->hours} hours.");
    }

    public function markPaid(Request $request, User $user): RedirectResponse
    {
        $request->validate(['period_start' => 'required|date']);

        PayrollPayment::updateOrCreate(
            ['user_id' => $user->id, 'period_start' => $request->period_start],
            ['paid_at' => now()]
        );

        return back()->with('success', "{$user->name} marked as paid.");
    }

    public function clearPaid(Request $request, User $user): RedirectResponse
    {
        $request->validate(['period_start' => 'required|date']);

        PayrollPayment::where('user_id', $user->id)
            ->where('period_start', $request->period_start)
            ->delete();

        return back()->with('success', "Payment status cleared for {$user->name}.");
    }

    public function export(Request $request)
    {
        [$defaultStart, $defaultEnd] = $this->currentPayPeriod();

        $startDate = $request->input('start_date', $defaultStart);
        $endDate   = $request->input('end_date', $defaultEnd);

        $trainers = $this->getReport($startDate, $endDate);

        $filename = "payroll_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($trainers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Venmo', 'Pay Rate', 'Sessions', 'Hours', 'Sparks Bonus', 'Services', 'Total Pay']);
            foreach ($trainers as $trainer) {
                $hours      = $trainer->hours_worked;
                $sparksBonus = $trainer->sparks_bonus ?? 0;
                $servicesPay = $trainer->recurring_services_pay ?? 0;
                $basePay    = $trainer->pay_rate ? round($hours * $trainer->pay_rate, 2) : 0;
                $totalPay   = $trainer->pay_rate ? round($basePay + $sparksBonus + $servicesPay, 2) : '';
                fputcsv($handle, [
                    $trainer->name,
                    $trainer->email,
                    $trainer->phone ?? '',
                    $trainer->venmo  ?? '',
                    $trainer->pay_rate ? number_format($trainer->pay_rate, 2) : '',
                    $trainer->sessions_count,
                    $hours,
                    $sparksBonus > 0 ? '$' . number_format($sparksBonus, 2) : '',
                    $servicesPay > 0 ? '$' . number_format($servicesPay, 2) : '',
                    $totalPay !== '' ? '$' . number_format($totalPay, 2) : '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Extra pay per hour for Sparks sessions
    private const SPARKS_PREMIUM = 5.00;

    private function getReport(string $startDate, string $endDate)
    {
        $today = Carbon::today()->toDateString();

        $trainers = User::where('role', 'trainer')
            ->withCount(['availabilities as sessions_count' => fn($q) =>
                $q->whereIn('status', ['assigned', 'confirmed'])
                  ->whereHas('trainingDay', fn($q2) =>
                      $q2->whereBetween('date', [$startDate, $endDate])
                        ->where('date', '<=', $today)
                  )
            ])
            ->with(['availabilities' => fn($q) =>
                $q->whereIn('status', ['assigned', 'confirmed'])
                  ->whereHas('trainingDay', fn($q2) =>
                      $q2->whereBetween('date', [$startDate, $endDate])
                        ->where('date', '<=', $today)
                  )
                  ->with('trainingDay')
            ])
            ->orderBy('name')
            ->get();

        // Load any manual hour overrides for this period
        $overrideRecords = PayrollHoursOverride::where('period_start', $startDate)->get()->keyBy('user_id');

        // Load payment records for this period
        $payments = PayrollPayment::where('period_start', $startDate)->get()->keyBy('user_id');

        // Load active recurring services, grouped by user
        $services = RecurringService::where('active', true)->with('user')->get()->groupBy('user_id');

        // All past training days in this period (for lead trainer auto-calculation)
        $allDaysInPeriod = \App\Models\TrainingDay::whereBetween('date', [$startDate, $endDate])
            ->where('date', '<=', $today)
            ->get();

        $weeks = (Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1) / 7;

        $trainers->each(function ($trainer) use ($overrideRecords, $payments, $services, $weeks, $allDaysInPeriod) {
            $override = $overrideRecords[$trainer->id] ?? null;

            if ($trainer->is_lead_trainer) {
                // Lead trainers are credited for ALL sessions in the period automatically
                $sparksHours    = $allDaysInPeriod->where('program', 'Sparks')->sum(fn($d) => $d->sessionHours());
                $nonSparksHours = $allDaysInPeriod->where('program', '!=', 'Sparks')->sum(fn($d) => $d->sessionHours());
                $planningHours  = $override ? (float) $override->planning_hours : 0.0;
                $trainer->sparks_hours     = round($sparksHours, 2);
                $trainer->non_sparks_hours = round($nonSparksHours, 2);
                $trainer->planning_hours   = round($planningHours, 2);
                $trainer->hours_worked     = round($sparksHours + $nonSparksHours + $planningHours, 2);
                $trainer->hours_calculated = round($sparksHours + $nonSparksHours, 2);
                $trainer->hours_override   = false;
                $trainer->manually_added   = false;
                $trainer->sparks_bonus     = $trainer->pay_rate ? round($sparksHours * self::SPARKS_PREMIUM, 2) : 0;
            } else {
                // Regular trainers: split availabilities into Sparks and non-Sparks
                $sparksHours    = $trainer->availabilities
                    ->filter(fn($a) => $a->trainingDay->program === 'Sparks')
                    ->sum(fn($a) => $a->hoursWorked());
                $nonSparksHours = $trainer->availabilities
                    ->filter(fn($a) => $a->trainingDay->program !== 'Sparks')
                    ->sum(fn($a) => $a->hoursWorked());
                $calculated = $sparksHours + $nonSparksHours;

                $trainer->sparks_hours     = round($sparksHours, 2);
                $trainer->non_sparks_hours = round($nonSparksHours, 2);
                $trainer->planning_hours   = 0;
                $trainer->hours_worked     = $override ? (float) $override->hours : round($calculated, 2);
                $trainer->hours_calculated = round($calculated, 2);
                $trainer->hours_override   = $override !== null;
                $trainer->manually_added   = $override !== null && $calculated == 0;
                $trainer->sparks_bonus     = $trainer->pay_rate ? round($sparksHours * self::SPARKS_PREMIUM, 2) : 0;
            }

            $trainer->paid_at                 = isset($payments[$trainer->id]) ? $payments[$trainer->id]->paid_at : null;
            $trainer->recurring_services      = $services[$trainer->id] ?? collect();
            $trainer->recurring_services_pay  = $trainer->recurring_services->sum(fn($s) => round($s->weekly_amount * $weeks, 2));
        });

        // Also surface trainers who have recurring services but no sessions this period
        $trainerIds       = $trainers->pluck('id');
        $serviceOnlyUsers = $services->keys()->diff($trainerIds);

        if ($serviceOnlyUsers->isNotEmpty()) {
            $extra = User::whereIn('id', $serviceOnlyUsers)->orderBy('name')->get();
            $extra->each(function ($trainer) use ($overrideRecords, $payments, $services, $weeks) {
                $trainer->sessions_count      = 0;
                $trainer->sparks_hours        = 0;
                $trainer->non_sparks_hours    = 0;
                $trainer->planning_hours      = 0;
                $trainer->hours_worked        = 0;
                $trainer->hours_override      = false;
                $trainer->hours_calculated    = 0;
                $trainer->manually_added      = false;
                $trainer->sparks_bonus        = 0;
                $trainer->availabilities      = collect();
                $trainer->paid_at             = isset($payments[$trainer->id]) ? $payments[$trainer->id]->paid_at : null;
                $trainer->recurring_services  = $services[$trainer->id];
                $trainer->recurring_services_pay = $trainer->recurring_services->sum(fn($s) => round($s->weekly_amount * $weeks, 2));
            });
            $trainers = $trainers->concat($extra)->sortBy('name')->values();
        }

        return $trainers;
    }

    private function previousPayPeriod(string $currentStart): array
    {
        $start = Carbon::parse($currentStart)->subDays(14);
        $end   = $start->copy()->addDays(13);
        return [$start->toDateString(), $end->toDateString()];
    }

    private function nextPayPeriod(string $currentStart): array
    {
        $start = Carbon::parse($currentStart)->addDays(14);
        $end   = $start->copy()->addDays(13);
        return [$start->toDateString(), $end->toDateString()];
    }
}
