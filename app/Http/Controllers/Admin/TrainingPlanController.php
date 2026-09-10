<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingDay;
use App\Models\TrainingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TrainingPlanController extends Controller
{
    public function index()
    {
        $plans = TrainingPlan::with('uploader')->orderBy('weekend_number')->get();

        $weekendNumbers = TrainingDay::select('weekend_number')
            ->distinct()
            ->orderBy('weekend_number')
            ->pluck('weekend_number');

        return view('admin.training-plans.index', compact('plans', 'weekendNumbers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'weekend_number' => 'required|integer|min:1|max:16',
            'program'        => 'required|in:main,sparks',
            'title'          => 'required|string|max:255',
            'file'           => 'required|file|mimes:pdf,xlsx,xls|max:20480',
        ]);

        // Replace existing plan for same weekend + program combination
        $existing = TrainingPlan::where('weekend_number', $request->weekend_number)
            ->where('program', $request->program)
            ->first();
        if ($existing) {
            try {
                Storage::delete($existing->file_path);
            } catch (\Exception $e) {
                Log::warning('Could not delete existing training plan file: ' . $e->getMessage());
            }
            $existing->delete();
        }

        $path = $request->file('file')->store('training-plans', config('filesystems.default'));

        if (! $path) {
            return back()->with('error', 'File upload failed. Please check your storage configuration and try again.');
        }

        TrainingPlan::create([
            'weekend_number' => $request->weekend_number,
            'program'        => $request->program,
            'title'          => $request->title,
            'file_path'      => $path,
            'uploaded_by'    => $request->user()->id,
        ]);

        return back()->with('success', 'Training plan uploaded successfully.');
    }

    public function copyToFall(Request $request): \Illuminate\Http\RedirectResponse
    {
        $springPlans = TrainingPlan::whereBetween('weekend_number', [1, 8])->get();

        if ($springPlans->isEmpty()) {
            return back()->with('error', 'No Spring plans (weekends 1–8) found to copy.');
        }

        $copied  = 0;
        $skipped = 0;

        foreach ($springPlans as $plan) {
            // Sparks started at Spring weekend 3, so offset is +6 to land on Fall weekend 9.
            // K/1st started at Spring weekend 1, so offset is +8.
            $offset      = $plan->program === 'sparks' ? 6 : 8;
            $fallWeekend = $plan->weekend_number + $offset;

            // Sparks only runs 6 Fall weekends (9–14); skip if out of range
            if ($plan->program === 'sparks' && $fallWeekend > 14) {
                $skipped++;
                continue;
            }

            // Skip if a Fall plan already exists for this weekend + program
            if (TrainingPlan::where('weekend_number', $fallWeekend)->where('program', $plan->program)->exists()) {
                $skipped++;
                continue;
            }

            // Copy the file to a new path
            $newPath = 'training-plans/' . basename($plan->file_path) . '_fall_copy_' . $fallWeekend;
            if (!Storage::exists($plan->file_path)) {
                $skipped++;
                continue;
            }
            Storage::copy($plan->file_path, $newPath);

            TrainingPlan::create([
                'weekend_number' => $fallWeekend,
                'program'        => $plan->program,
                'title'          => $plan->title,
                'file_path'      => $newPath,
                'uploaded_by'    => $request->user()->id,
            ]);

            $copied++;
        }

        $msg = "Copied {$copied} plan(s) to Fall weekends 9–16.";
        if ($skipped) {
            $msg .= " Skipped {$skipped} (already existed or file missing).";
        }

        return back()->with('success', $msg);
    }

    public function destroy(TrainingPlan $trainingPlan)
    {
        try {
            Storage::delete($trainingPlan->file_path);
        } catch (\Exception $e) {
            Log::warning('Could not delete training plan file: ' . $e->getMessage());
        }

        $trainingPlan->delete();

        return back()->with('success', 'Training plan deleted.');
    }
}
