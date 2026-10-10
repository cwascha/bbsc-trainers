<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    public function edit()
    {
        return view('admin.club.edit', ['club' => currentClub()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $club = currentClub();

        $request->validate([
            'name'          => 'required|string|max:100',
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'  => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $club->logo_path = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        $club->fill([
            'name'          => $request->name,
            'primary_color' => $request->primary_color,
            'accent_color'  => $request->accent_color,
        ])->save();

        if (session('welcome')) {
            return redirect()->route('admin.dashboard')->with('success', 'Club branding saved! Welcome to TrainerSync.');
        }

        return back()->with('success', 'Branding updated successfully.');
    }

    public function dismissWelcome(): RedirectResponse
    {
        session(['welcome_dismissed' => true]);
        return redirect()->route('admin.dashboard');
    }
}
