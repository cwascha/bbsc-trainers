<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollHoursOverride;
use App\Models\PayrollPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Fixed anchor date for biweekly pay period calculation
    private static string $ANCHOR = '2026-01-01';

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
            'period_start' => 'required|date',
            'hours'        => 'required|numeric|min:0|max:999',
        ]);

        PayrollHoursOverride::updateOrCreate(
            ['user_id' => $user->id, 'period_start' => $request->period_start],
            ['hours'   => $request->hours]
        );

        return back()->with('success', "Hours updated for {$user->name}.");
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
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Venmo', 'Pay Rate', 'Sessions', 'Hours', 'Total Pay']);
            foreach ($trainers as $trainer) {
                $hours    = $trainer->hours_worked;
                $totalPay = $trainer->pay_rate ? round($hours * $trainer->pay_rate, 2) : '';
                fputcsv($handle, [
                    $trainer->name,
                    $trainer->email,
                    $trainer->phone ?? '',
                    $trainer->venmo  ?? '',
                    $trainer->pay_rate ? number_format($trainer->pay_rate, 2) : '',
                    $trainer->sessions_count,
                    $hours,
                    $totalPay !== '' ? '$' . number_format($totalPay, 2) : '',
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

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
        $overrides = PayrollHoursOverride::where('period_start', $startDate)
            ->pluck('hours', 'user_id');

        // Load payment records for this period
        $payments = PayrollPayment::where('period_start', $startDate)
            ->get()
            ->keyBy('user_id');

        // Calculate hours; use manual override if one exists
        $trainers->each(function ($trainer) use ($overrides, $payments, $today) {
            // Future days already excluded by the eager-load query above.
            // hoursWorked() returns hours_override if set, otherwise the day default.
            $calculated                = $trainer->availabilities
                ->sum(fn($a) => $a->hoursWorked());
            $trainer->hours_worked     = isset($overrides[$trainer->id])
                ? (float) $overrides[$trainer->id]
                : $calculated;
            $trainer->hours_override   = isset($overrides[$trainer->id]);
            $trainer->hours_calculated = $calculated;
            // "manually added" = has an override but no actual sessions in this period
            $trainer->manually_added   = isset($overrides[$trainer->id]) && $calculated == 0;
            $trainer->paid_at          = isset($payments[$trainer->id])
                ? $payments[$trainer->id]->paid_at
                : null;
        });

        return $trainers;
    }

    private function currentPayPeriod(): array
    {
        $anchor  = Carbon::parse(self::$ANCHOR);
        $today   = Carbon::today();
        $days    = $anchor->diffInDays($today, false);
        $period  = (int) floor(max($days, 0) / 14);
        $start   = $anchor->copy()->addDays($period * 14);
        $end     = $start->copy()->addDays(13);
        return [$start->toDateString(), $end->toDateString()];
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
