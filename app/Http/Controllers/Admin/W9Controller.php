<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class W9Controller extends Controller
{
    public function download(User $user): mixed
    {
        if (! $user->w9_path || ! Storage::exists($user->w9_path)) {
            return back()->with('error', 'W9 file not found.');
        }

        return Storage::download($user->w9_path, $user->name . ' W9.pdf');
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
