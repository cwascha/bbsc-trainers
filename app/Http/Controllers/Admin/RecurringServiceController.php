<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecurringService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RecurringServiceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'description'   => 'required|string|max:100',
            'weekly_amount' => 'required|numeric|min:0.01|max:99999',
        ]);

        $user = User::findOrFail($request->user_id);

        RecurringService::create([
            'user_id'       => $user->id,
            'description'   => trim($request->description),
            'weekly_amount' => $request->weekly_amount,
            'active'        => true,
        ]);

        return back()->with('success', "Recurring service \"{$request->description}\" added for {$user->name}.");
    }

    public function toggle(RecurringService $recurringService): RedirectResponse
    {
        $recurringService->update(['active' => ! $recurringService->active]);
        $state = $recurringService->active ? 'activated' : 'paused';
        return back()->with('success', "\"{$recurringService->description}\" {$state} for {$recurringService->user->name}.");
    }

    public function destroy(RecurringService $recurringService): RedirectResponse
    {
        $name = $recurringService->user->name;
        $desc = $recurringService->description;
        $recurringService->delete();
        return back()->with('success', "\"{$desc}\" removed for {$name}.");
    }
}
