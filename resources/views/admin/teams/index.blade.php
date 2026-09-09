<x-admin-layout>
    <x-slot name="header">Teams &amp; Rosters</x-slot>

    <div class="space-y-6">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Google Sheets sync --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800 text-lg">Roster Sync</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Pulls player rosters from Google Sheets and updates the database.
                        Each tab should match a program name (Sparks, Kindergarten, 1st Grade).<br>
                        <strong>Expected columns:</strong> <code class="bg-gray-100 px-1 rounded">Name</code>,
                        <code class="bg-gray-100 px-1 rounded">Role</code> (put "Coach" for the coach row),
                        <code class="bg-gray-100 px-1 rounded">Jersey</code> (optional).
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.teams.sync') }}" class="ml-4 shrink-0">
                    @csrf
                    <button class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 whitespace-nowrap">
                        ↻ Sync Now
                    </button>
                </form>
            </div>

            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-800 space-y-1">
                <p class="font-medium">Setup required (.env / Laravel Cloud env vars):</p>
                <ul class="list-disc list-inside space-y-0.5 text-blue-700">
                    <li><code>GOOGLE_SPREADSHEET_ID</code> — the ID from your sheet's URL</li>
                    <li><code>GOOGLE_CREDENTIALS_JSON</code> — paste the entire service account JSON (minified)</li>
                    <li><code>GOOGLE_TAB_SPARKS</code>, <code>GOOGLE_TAB_KINDERGARTEN</code>, <code>GOOGLE_TAB_1ST_GRADE</code> — tab names (defaults: Sparks, Kindergarten, 1st Grade)</li>
                </ul>
                <p class="mt-2">Share your Google Sheet with the service account email (<code>...iam.gserviceaccount.com</code>) as a Viewer.</p>
            </div>
        </div>

        {{-- Team cards --}}
        @forelse($teams as $team)
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $team->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $team->program_label }} · {{ $team->players->count() }} players</p>
                </div>
                @if($team->coach_name)
                    <span class="text-sm text-gray-600">Coach: <strong>{{ $team->coach_name }}</strong></span>
                @else
                    <span class="text-sm text-amber-500 italic">Coach TBD</span>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.teams.update', $team) }}" class="px-6 py-5">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Team Name</label>
                        <input type="text" name="name" value="{{ old('name', $team->name) }}"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Coach Name</label>
                        <input type="text" name="coach_name" value="{{ old('coach_name', $team->coach_name) }}"
                               placeholder="Pulled from sheet sync"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Coach Email</label>
                        <input type="email" name="coach_email" value="{{ old('coach_email', $team->coach_email) }}"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Format (e.g. 4 v 4)</label>
                        <input type="text" name="format" value="{{ old('format', $team->format) }}"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Location</label>
                        <input type="text" name="location" value="{{ old('location', $team->location) }}"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Session Times</label>
                        <input type="text" name="session_times" value="{{ old('session_times', $team->session_times) }}"
                               placeholder="e.g. Sat 9:00–12:00"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Rules URL</label>
                        <input type="url" name="rules_url" value="{{ old('rules_url', $team->rules_url) }}"
                               placeholder="https://bbscsoccer.com/rules"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Schedule URL</label>
                        <input type="url" name="schedule_url" value="{{ old('schedule_url', $team->schedule_url) }}"
                               placeholder="https://bbscsoccer.com/schedule"
                               class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs text-gray-500 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                                  class="w-full border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">{{ old('notes', $team->notes) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="submit"
                            class="px-4 py-1.5 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                        Save
                    </button>
                    @if($team->players->count())
                        <span class="text-xs text-gray-400">{{ $team->players->count() }} players synced from sheet</span>
                    @else
                        <span class="text-xs text-amber-500">No players — run Sync Now after configuring credentials</span>
                    @endif
                </div>
            </form>
        </div>
        @empty
            <div class="bg-white rounded-lg shadow px-6 py-8 text-center text-gray-400">
                <p>No teams yet. Click <strong>Sync Now</strong> to import rosters from Google Sheets.</p>
                <p class="mt-1 text-sm">Teams are created automatically for each program tab in your sheet.</p>
            </div>
        @endforelse

    </div>
</x-admin-layout>
