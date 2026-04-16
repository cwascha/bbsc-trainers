<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
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

        [$prevStart, $prevEnd] = $this->previousPayPeriod($startDate);

        return view('admin.reports.index', compact(
            'trainers', 'startDate', 'endDate', 'prevStart', 'prevEnd'
        ));
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
        $trainers = User::where('role', 'trainer')
            ->withCount(['availabilities as sessions_count' => fn($q) =>
                $q->whereIn('status', ['assigned', 'confirmed'])
                  ->whereHas('trainingDay', fn($q2) =>
                      $q2->whereBetween('date', [$startDate, $endDate])
                  )
            ])
            ->with(['availabilities' => fn($q) =>
                $q->whereIn('status', ['assigned', 'confirmed'])
                  ->whereHas('trainingDay', fn($q2) =>
                      $q2->whereBetween('date', [$startDate, $endDate])
                  )
                  ->with('trainingDay')
            ])
            ->orderBy('name')
            ->get();

        // Calculate actual hours from each training day's session times
        $trainers->each(function ($trainer) {
            $trainer->hours_worked = $trainer->availabilities
                ->sum(fn($a) => $a->trainingDay->sessionHours());
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
}
