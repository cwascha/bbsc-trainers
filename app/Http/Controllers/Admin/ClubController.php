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
            'name'                 => 'required|string|max:100',
            'primary_color'        => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accent_color'         => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo'                 => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'roster_notify_phones'  => 'nullable|string|max:500',
            'timezone'              => 'nullable|string|max:50',
            'teams_field_name'      => 'nullable|string|max:200',
            'teams_field_map_query' => 'nullable|string|max:300',
            'teams_rules_url'       => 'nullable|url|max:500',
            'teams_schedule_url'    => 'nullable|url|max:500',
            'teams_jersey_info'     => 'nullable|string|max:500',
            'teams_head_trainer'    => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $club->logo_path = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        $club->fill([
            'name'                 => $request->name,
            'primary_color'        => $request->primary_color,
            'accent_color'         => $request->accent_color,
            'roster_notify_phones' => $request->roster_notify_phones,
            'timezone'              => $request->timezone ?: 'America/New_York',
            'teams_field_name'      => $request->teams_field_name ?: null,
            'teams_field_map_query' => $request->teams_field_map_query ?: null,
            'teams_rules_url'       => $request->teams_rules_url ?: null,
            'teams_schedule_url'    => $request->teams_schedule_url ?: null,
            'teams_jersey_info'     => $request->teams_jersey_info ?: null,
            'teams_head_trainer'    => $request->teams_head_trainer ?: null,
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
