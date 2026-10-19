<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class W9Controller extends Controller
{
    public function download(User $user): mixed
    {
        if (! $user->w9_path || ! Storage::exists($user->w9_path)) {
            return back()->with('error', 'W9 file not found.');
        }

        return Storage::download($user->w9_path, $user->name . ' W9.pdf');
    }

    // Admin uploads a W9 on the trainer's behalf (e.g. received by email or on paper)
    public function upload(Request $request, User $user): RedirectResponse
    {
        // The admin layout only renders flash errors, so surface validation failures that way
        $validator = Validator::make($request->all(), [
            'w9' => 'required|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return back()->with('error', "{$user->name}'s W9: " . $validator->errors()->first('w9'));
        }

        if ($user->w9_path) {
            Storage::delete($user->w9_path);
        }

        $path = $request->file('w9')->storeAs(
            'w9s',
            $user->id . '_w9.pdf'
        );

        // The admin has the form in hand, so it counts as received
        $user->update([
            'w9_path'        => $path,
            'w9_uploaded_at' => now(),
            'w9_received_at' => now(),
        ]);

        return back()->with('success', "{$user->name}'s W9 uploaded and marked as received.");
    }

    public function markReceived(User $user): RedirectResponse
    {
        $nowReceived = ! $user->w9_received_at;

        $user->update(['w9_received_at' => $nowReceived ? now() : null]);

        $msg = $nowReceived
            ? "{$user->name}'s W9 marked as received."
            : "{$user->name}'s W9 unmarked.";

        return back()->with('success', $msg);
    }
}
