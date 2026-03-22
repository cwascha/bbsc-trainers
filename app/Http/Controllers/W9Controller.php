<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class W9Controller extends Controller
{
    public function template(): BinaryFileResponse
    {
        return response()->download(public_path('downloads/w9-template.pdf'), 'IRS-W9.pdf');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'w9' => 'required|file|mimes:pdf|max:5120',
        ]);

        $user = $request->user();

        // Delete old file if exists
        if ($user->w9_path) {
            Storage::delete($user->w9_path);
        }

        $path = $request->file('w9')->storeAs(
            'w9s',
            $user->id . '_w9.pdf'
        );

        $user->update([
            'w9_path'        => $path,
            'w9_uploaded_at' => now(),
            'w9_received_at' => null, // reset received if re-uploading
        ]);

        return back()->with('success', 'Your W9 has been uploaded successfully.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->w9_path) {
            Storage::delete($user->w9_path);
        }

        $user->update([
            'w9_path'        => null,
            'w9_uploaded_at' => null,
            'w9_received_at' => null,
        ]);

        return back()->with('success', 'Your W9 has been removed.');
    }
}
