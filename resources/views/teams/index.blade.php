@extends('layouts.public')

@section('title', ($currentClub->name ?? config('app.name')) . ' Teams')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Fall 2026 Teams</h1>
    <p class="mt-1 text-gray-500">Rosters and program details for the {{ $currentClub->name ?? config('app.name') }} season.</p>
</div>

@php
    // Build player index for JS
    $playerIndex = [];
    foreach ($groups as $groupName => $teams) {
        foreach ($teams as $team) {
            foreach ($team->players as $player) {
                $playerIndex[] = [
                    'name' => $player->full_name,
                    'team' => $team->name,
                    'group' => $groupName,
                    'url'  => route('teams.public.show', $team),
                ];
            }
        }
    }
@endphp
<script>
const PLAYERS = @json($playerIndex);

document.addEventListener('DOMContentLoaded', () => {
    const input   = document.getElementById('player-search');
    const results = document.getElementById('search-results');

    input.addEventListener('input', () => {
        const q = input.value.trim().toLowerCase();
        if (q.length < 2) { results.classList.add('hidden'); results.innerHTML = ''; return; }

        const matches = PLAYERS.filter(p => p.name.toLowerCase().includes(q)).slice(0, 20);

        function highlight(text, query) {
            const idx = text.toLowerCase().indexOf(query);
            if (idx === -1) return text;
            return text.slice(0, idx)
                + '<mark class="bg-yellow-200 text-gray-900 rounded px-0.5">' + text.slice(idx, idx + query.length) + '</mark>'
                + text.slice(idx + query.length);
        }

        if (!matches.length) {
            results.innerHTML = '<p class="px-4 py-3 text-sm text-gray-400">No players found.</p>';
        } else {
            results.innerHTML = matches.map(p => `
                <a href="${p.url}?player=${encodeURIComponent(p.name)}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-0">
                    <span class="font-medium text-gray-900 text-sm">${highlight(p.name, q)}</span>
                    <span class="text-xs text-gray-500 ml-4 shrink-0">${p.team} · ${p.group}</span>
                </a>`).join('');
        }
        results.classList.remove('hidden');
    });

    document.addEventListener('click', e => {
        if (!document.getElementById('search-container').contains(e.target)) {
            results.classList.add('hidden');
        }
    });
});
</script>

{{-- ── Information Section (only shown if club has configured any fields) ── --}}
@if($currentClub->teams_field_name || $currentClub->teams_rules_url || $currentClub->teams_schedule_url || $currentClub->teams_jersey_info || $currentClub->teams_head_trainer)
<div class="mb-10 bg-white rounded-xl shadow overflow-hidden">

    <div class="bg-gray-800 px-6 py-4">
        <h2 class="text-xl font-bold text-white">Information</h2>
    </div>

    <div class="divide-y divide-gray-100">

        {{-- Field Location --}}
        @if($currentClub->teams_field_name)
        <div class="px-6 py-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Field Location</h3>
            <p class="text-gray-900 font-medium mb-4">{{ $currentClub->teams_field_name }}</p>
            @if($currentClub->teams_field_map_query)
            <div class="rounded-xl overflow-hidden border border-gray-200" style="height:200px;">
                <iframe
                    title="{{ $currentClub->teams_field_name }} Map"
                    width="100%" height="100%"
                    style="border:0;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://maps.google.com/maps?q={{ urlencode($currentClub->teams_field_map_query) }}&t=&z=15&ie=UTF8&iwloc=&output=embed">
                </iframe>
            </div>
            @endif
        </div>
        @endif

        {{-- Links --}}
        @if($currentClub->teams_rules_url || $currentClub->teams_schedule_url)
        <div class="px-6 py-5 flex flex-wrap gap-4">
            @if($currentClub->teams_rules_url)
            <a href="{{ $currentClub->teams_rules_url }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                League Rules
            </a>
            @endif
            @if($currentClub->teams_schedule_url)
            <a href="{{ $currentClub->teams_schedule_url }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Schedules
            </a>
            @endif
        </div>
        @endif

        {{-- Jerseys --}}
        @if($currentClub->teams_jersey_info)
        <div class="px-6 py-5">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Jerseys</h3>
            <p class="text-sm text-gray-700">{{ $currentClub->teams_jersey_info }}</p>
        </div>
        @endif

        {{-- Head Trainer --}}
        @if($currentClub->teams_head_trainer)
        <div class="px-6 py-5 bg-green-50 flex items-start gap-3">
            <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <p class="text-sm text-green-800">
                Our head trainer is <strong>{{ $currentClub->teams_head_trainer }}</strong> — you can find them on the field every week.
            </p>
        </div>
        @endif

    </div>
</div>
@endif
{{-- ── End Information Section ──────────────────────────────────────────── --}}

{{-- Player search --}}
<div class="mb-8 bg-blue-600 rounded-xl shadow-md px-6 py-5">
    <p class="text-white font-semibold text-base mb-3">
        <svg class="inline w-4 h-4 mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
        </svg>
        Find your child's team
    </p>
    <div class="relative" id="search-container">
        <input type="text" id="player-search" placeholder="Type your child's name…"
               autocomplete="off"
               class="w-full rounded-xl px-4 py-3 pl-10 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-white border-0">
        <svg class="absolute left-3 top-3.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
        </svg>
        <div id="search-results"
             class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg hidden max-h-80 overflow-y-auto">
        </div>
    </div>
</div>

@if($groups->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
        <p class="text-lg">Rosters haven't been published yet — check back soon!</p>
    </div>
@else
    @php
        $groupColors = [
            'Kindergarten Girls' => ['bg' => 'bg-pink-50',   'border' => 'border-pink-200',   'header' => 'bg-pink-600',   'badge' => 'bg-pink-100 text-pink-700'],
            'Kindergarten Boys'  => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'header' => 'bg-blue-600',   'badge' => 'bg-blue-100 text-blue-700'],
            '1st Grade Girls'    => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'header' => 'bg-purple-600', 'badge' => 'bg-purple-100 text-purple-700'],
            '1st Grade Boys'     => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'header' => 'bg-green-600',  'badge' => 'bg-green-100 text-green-700'],
        ];
    @endphp

    <div class="space-y-10">
        @foreach($groups as $groupName => $teams)
        @php $c = $groupColors[$groupName] ?? ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'header' => 'bg-gray-600', 'badge' => 'bg-gray-100 text-gray-700']; @endphp

        <div>
            <div class="{{ $c['header'] }} text-white rounded-t-xl px-6 py-4">
                <h2 class="text-xl font-bold">{{ $groupName }}</h2>
                <p class="text-sm opacity-75">{{ $teams->count() }} teams · {{ $teams->sum(fn($t) => $t->players->count()) }} players</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                @foreach($teams as $team)
                <a href="{{ route('teams.public.show', $team) }}"
                   class="block {{ $c['bg'] }} border {{ $c['border'] }} rounded-xl p-5 hover:shadow-md transition-shadow">
                    <h3 class="font-bold text-gray-900 text-lg">{{ $team->name }}</h3>

                    @if($team->coach_name)
                        <p class="text-sm text-gray-600 mt-1">Coach: <span class="font-medium">{{ $team->coach_name }}</span></p>
                    @else
                        <p class="text-sm text-amber-600 mt-1 italic">Coach TBD</p>
                    @endif

                    <p class="text-sm text-gray-500 mt-2">{{ $team->players->count() }} players →</p>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection
