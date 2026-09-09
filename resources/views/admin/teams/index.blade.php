@extends('layouts.admin')

@section('content')
<div class="space-y-6">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Excel import --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-800 text-lg mb-1">Import Rosters from Spreadsheet</h2>
            <p class="text-sm text-gray-500 mb-4">
                Upload the BBSC roster <code class="bg-gray-100 px-1 rounded">.xlsx</code> file directly.
                The importer reads the <strong>Kindergarten Girls</strong>, <strong>Kindergarten Boys</strong>,
                <strong>1st Grade Girls</strong>, <strong>1st Grade Boys</strong>, and <strong>Coaches</strong> tabs automatically.
                <span class="text-red-500">Uploading replaces all existing rosters.</span>
            </p>

            <form method="POST" action="{{ route('admin.teams.import') }}" enctype="multipart/form-data"
                  class="flex items-center gap-3">
                @csrf
                <input type="file" name="roster" accept=".xlsx,.xls" required
                       class="block text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 whitespace-nowrap">
                    Upload &amp; Import
                </button>
            </form>
        </div>

        {{-- Teams by group --}}
        @forelse($teams as $groupName => $groupTeams)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                <h2 class="font-semibold text-gray-700">{{ $groupName ?: 'Uncategorized' }}</h2>
                <p class="text-xs text-gray-400">{{ $groupTeams->count() }} teams · {{ $groupTeams->sum(fn($t) => $t->players->count()) }} players</p>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($groupTeams as $team)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h3 class="font-medium text-gray-800">{{ $team->name }}</h3>
                            <p class="text-xs text-gray-400">{{ $team->players->count() }} players</p>
                        </div>
                        @if($team->coach_name)
                            <span class="text-sm text-gray-600">Coach: <strong>{{ $team->coach_name }}</strong></span>
                        @else
                            <span class="text-sm text-amber-500 italic">No coach assigned</span>
                        @endif
                    </div>

                    {{-- Program-level format details (editable per team) --}}
                    <form method="POST" action="{{ route('admin.teams.update', $team) }}">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Coach Override</label>
                                <input type="text" name="coach_name" value="{{ old('coach_name', $team->coach_name) }}"
                                       placeholder="From spreadsheet"
                                       class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Format</label>
                                <input type="text" name="format" value="{{ old('format', $team->format) }}"
                                       placeholder="e.g. 4 v 4"
                                       class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Rules URL</label>
                                <input type="url" name="rules_url" value="{{ old('rules_url', $team->rules_url) }}"
                                       class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Schedule URL</label>
                                <input type="url" name="schedule_url" value="{{ old('schedule_url', $team->schedule_url) }}"
                                       class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="submit" class="px-3 py-1 bg-gray-800 text-white text-xs rounded hover:bg-gray-700">Save</button>
                        </div>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @empty
            <div class="bg-white rounded-lg shadow px-6 py-10 text-center text-gray-400">
                <p class="text-lg">No teams yet.</p>
                <p class="mt-1 text-sm">Upload the roster spreadsheet above to import all teams and players at once.</p>
            </div>
        @endforelse

    </div>

@endsection
