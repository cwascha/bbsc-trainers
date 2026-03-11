<?php

namespace App\Http\Controllers;

use App\Models\TrainingDay;
use App\Models\TrainingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingPlanController extends Controller
{
    public function index()
    {
        $plans = TrainingPlan::with('uploader')
            ->orderBy('weekend_number')
            ->get();

        return view('training-plans.index', compact('plans'));
    }

    public function download(TrainingPlan $trainingPlan)
    {
        if (! Storage::exists($trainingPlan->file_path)) {
            abort(404, 'Training plan file not found.');
        }

        return Storage::download($trainingPlan->file_path, $trainingPlan->title . '.pdf');
    }
}
