<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingDay;
use App\Models\TrainingPlan;
use Illuminate\Http\Request;
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
            'weekend_number' => 'required|integer|min:1|max:8',
            'title'          => 'required|string|max:255',
            'file'           => 'required|file|mimes:pdf|max:20480',
        ]);

        // Replace existing plan for same weekend
        $existing = TrainingPlan::where('weekend_number', $request->weekend_number)->first();
        if ($existing) {
            Storage::delete($existing->file_path);
            $existing->delete();
        }

        $path = $request->file('file')->store('training-plans');

        TrainingPlan::create([
            'weekend_number' => $request->weekend_number,
            'title'          => $request->title,
            'file_path'      => $path,
            'uploaded_by'    => $request->user()->id,
        ]);

        return back()->with('success', 'Training plan uploaded successfully.');
    }

    public function destroy(TrainingPlan $trainingPlan)
    {
        Storage::delete($trainingPlan->file_path);
        $trainingPlan->delete();

        return back()->with('success', 'Training plan deleted.');
    }
}
