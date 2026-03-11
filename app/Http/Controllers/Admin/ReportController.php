<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Availability;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfWeek()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfWeek()->toDateString());

        $trainers = $this->getReport($startDate, $endDate);

        return view('admin.reports.index', compact('trainers', 'startDate', 'endDate'));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfWeek()->toDateString());
        $endDate   = $request->input('end_date', now()->endOfWeek()->toDateString());

        $trainers = $this->getReport($startDate, $endDate);

        $filename = "payroll_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($trainers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Phone', 'Sessions Worked', 'Total Hours']);
            foreach ($trainers as $trainer) {
                fputcsv($handle, [
                    $trainer->name,
                    $trainer->email,
                    $trainer->phone ?? '',
                    $trainer->sessions_count,
                    $trainer->sessions_count * 7,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getReport(string $startDate, string $endDate)
    {
        return User::where('role', 'trainer')
            ->withCount(['availabilities as sessions_count' => fn($q) =>
                $q->whereIn('status', ['assigned', 'confirmed'])
                  ->whereHas('trainingDay', fn($q2) =>
                      $q2->whereBetween('date', [$startDate, $endDate])
                  )
            ])
            ->orderBy('name')
            ->get();
    }
}
