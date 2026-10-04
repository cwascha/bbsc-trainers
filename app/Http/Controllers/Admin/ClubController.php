<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            if ($club->logo_path) {
                Storage::disk('public')->delete($club->logo_path);
            }
            $club->logo_path = $request->file('logo')->store("logos/{$club->id}", 'public');
        }

        $club->fill([
            'name'          => $request->name,
            'primary_color' => $request->primary_color,
            'accent_color'  => $request->accent_color,
        ])->save();

        return back()->with('success', 'Branding updated successfully.');
    }
}
