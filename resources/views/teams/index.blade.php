@extends('layouts.public')

@section('title', 'BBSC Teams — Fall 2026')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Fall 2026 Teams</h1>
    <p class="mt-1 text-gray-500">Rosters and program details for the BBSC Fall 2026 season.</p>
</div>

{{-- Player search --}}
<div class="mb-8 relative" id="search-container">
    <div class="relative">
        <input type="text" id="player-search" placeholder="Search for your child by name…"
               autocomplete="off"
               class="w-full border border-gray-300 rounded-xl px-4 py-3 pl-10 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent">
        <svg class="absolute left-3 top-3.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z"/>
        </svg>
    </div>
    <div id="search-results"
         class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-xl shadow-lg hidden max-h-80 overflow-y-auto">
    </div>
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
</script>

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
