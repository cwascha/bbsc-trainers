@extends('layouts.public')

@section('title', $team->name . ' — BBSC Fall 2026')

@section('content')

<div class="mb-6">
    <a href="{{ route('teams.public.index') }}" class="text-sm text-blue-600 hover:underline">← All Teams</a>
</div>

@php
    $groupColors = [
        'Kindergarten Girls' => ['header' => 'bg-pink-600'],
        'Kindergarten Boys'  => ['header' => 'bg-blue-600'],
        '1st Grade Girls'    => ['header' => 'bg-purple-600'],
        '1st Grade Boys'     => ['header' => 'bg-green-600'],
    ];
    $c = $groupColors[$team->group_name] ?? ['header' => 'bg-gray-600'];
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="{{ $c['header'] }} px-6 py-5">
            <p class="text-sm text-white/70 mb-0.5">{{ $team->group_name }}</p>
            <h1 class="text-2xl font-bold text-white">{{ $team->name }}</h1>
        </div>
        <div class="px-6 py-5 text-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">Coach</p>
                    @if($team->coach_name)
                        <p class="text-gray-900 font-medium">{{ $team->coach_name }}</p>
                    @else
                        <p class="text-amber-600 italic">Coach TBD</p>
                    @endif
                </div>
                @if($team->session_times)
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">Session Times</p>
                    <p class="text-gray-800">{{ $team->session_times }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Format / links --}}
    @if($team->format || $team->location || $team->rules_url || $team->schedule_url || $team->notes)
    <div class="bg-white rounded-xl shadow">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Program Details</h2>
        </div>
        <div class="px-6 py-5 space-y-3 text-sm">
            @if($team->format)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Format</span>
                <span class="text-gray-800 font-medium">{{ $team->format }}</span>
            </div>
            @endif
            @if($team->location)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Location</span>
                <span class="text-gray-800">{{ $team->location }}</span>
            </div>
            @endif
            @if($team->rules_url)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Rules</span>
                <a href="{{ $team->rules_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">View Rules →</a>
            </div>
            @endif
            @if($team->schedule_url)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Schedule</span>
                <a href="{{ $team->schedule_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline">View Schedule →</a>
            </div>
            @endif
            @if($team->notes)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Notes</span>
                <span class="text-gray-800">{{ $team->notes }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Roster --}}
    <div class="bg-white rounded-xl shadow">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Roster</h2>
            <span class="text-sm text-gray-400">{{ $team->players->count() }} players</span>
        </div>
        @if($team->players->isEmpty())
            <div class="px-6 py-8 text-center text-gray-400">Roster not yet published.</div>
        @else
            @php $highlight = request('player'); @endphp
            <ul class="divide-y divide-gray-100">
                @foreach($team->players as $player)
                @php $isHighlighted = $highlight && strcasecmp($player->full_name, $highlight) === 0; @endphp
                <li id="{{ $isHighlighted ? 'highlighted-player' : '' }}"
                    class="px-6 py-3 {{ $isHighlighted ? 'bg-yellow-100 font-semibold text-gray-900 ring-1 ring-yellow-300 rounded-lg' : 'text-gray-800' }}">
                    {{ $player->full_name }}
                </li>
                @endforeach
            </ul>
            @if($highlight)
            <script>
                const el = document.getElementById('highlighted-player');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            </script>
            @endif
        @endif
    </div>

</div>

@endsection
