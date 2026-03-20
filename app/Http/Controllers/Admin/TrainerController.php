<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:30',
        ]);

        $name = trim($request->first_name . ' ' . $request->last_name);

        User::create([
            'name'              => $name,
            'email'             => strtolower(trim($request->email)),
            'phone'             => $request->phone ?: null,
            'role'              => 'trainer',
            'password'          => Str::random(24),
            'email_verified_at' => now(),
        ]);

        return back()->with('success', "Trainer {$name} added. They can use \"Forgot Password\" to set their password.");
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $handle = fopen($request->file('file')->getRealPath(), 'r');

        // Read and normalize header row
        $rawHeaders = fgetcsv($handle);
        if ($rawHeaders === false) {
            fclose($handle);
            return back()->with('error', 'The file appears to be empty.');
        }
        $headers = array_map(fn($h) => strtolower(trim($h)), $rawHeaders);

        $col = fn(array $names) => collect($names)
            ->map(fn($n) => array_search($n, $headers))
            ->first(fn($i) => $i !== false);

        $firstIdx = $col(['first name', 'firstname', 'first_name', 'name']);
        $lastIdx  = $col(['last name', 'lastname', 'last_name']);
        $emailIdx = $col(['email', 'email address', 'email_address']);
        $phoneIdx = $col(['phone', 'phone number', 'phone_number', 'mobile', 'cell']);

        $created = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Skip rows with no email or invalid email
            $email = $emailIdx !== null ? strtolower(trim($row[$emailIdx] ?? '')) : '';
            if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $firstName = $firstIdx !== null ? trim($row[$firstIdx] ?? '') : '';
            $lastName  = $lastIdx  !== null ? trim($row[$lastIdx]  ?? '') : '';
            $name      = trim("$firstName $lastName") ?: $email;
            $phone     = $phoneIdx !== null ? trim($row[$phoneIdx] ?? '') : null;

            User::create([
                'name'              => $name,
                'email'             => $email,
                'phone'             => $phone ?: null,
                'role'              => 'trainer',
                'password'          => Str::random(24),
                'email_verified_at' => now(),
            ]);

            $created++;
        }

        fclose($handle);

        return back()->with('success', "Import complete: {$created} trainer(s) added, {$skipped} skipped (already exist). Imported trainers can use \"Forgot Password\" to set their password.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== 'trainer') {
            return back()->with('error', 'Only trainer accounts can be removed.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        $name = $user->name;
        $user->availabilities()->delete();
        $user->delete();

        return back()->with('success', "{$name} has been removed.");
    }
}
