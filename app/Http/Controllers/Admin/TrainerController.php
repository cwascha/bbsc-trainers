<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class TrainerController extends Controller
{
    public function index()
    {
        $trainers = User::where('role', 'trainer')
            ->withCount(['availabilities as sessions_worked' => fn($q) =>
                $q->whereIn('status', ['assigned', 'confirmed'])
                  ->whereHas('trainingDay', fn($q2) => $q2->where('date', '<', now()->toDateString()))
            ])
            ->orderBy('name')
            ->get();

        return view('admin.trainers.index', compact('trainers'));
    }
}
