@extends('layouts.public')

@section('title', $team->name . ' — BBSC Fall 2026')

@section('content')

<div class="mb-6">
    <a href="{{ route('teams.public.index') }}" class="text-sm text-blue-600 hover:underline">← All Teams</a>
</div>

@php
    $colors = [
        'sparks'       => ['badge' => 'bg-orange-100 text-orange-700', 'header' => 'bg-orange-600'],
        'kindergarten' => ['badge' => 'bg-blue-100 text-blue-700',     'header' => 'bg-blue-600'],
        '1st_grade'    => ['badge' => 'bg-green-100 text-green-700',   'header' => 'bg-green-600'],
    ];
    $c = $colors[$team->program] ?? ['badge' => 'bg-gray-100 text-gray-700', 'header' => 'bg-gray-600'];
@endphp

<div class="space-y-6">

    {{-- Team header --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="{{ $c['header'] }} px-6 py-5">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white">{{ $team->name }}</h1>
                <span class="px-2 py-0.5 bg-white/20 text-white rounded text-sm">{{ $team->program_label }}</span>
            </div>
        </div>
        <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-0.5">Coach</p>
                @if($team->coach_name)
                    <p class="text-gray-900 font-medium">{{ $team->coach_name }}</p>
                    @if($team->coach_email)
                        <a href="mailto:{{ $team->coach_email }}" class="text-blue-600 hover:underline">{{ $team->coach_email }}</a>
                    @endif
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

    {{-- Format info --}}
    @if($team->format || $team->location || $team->rules_url || $team->schedule_url || $team->notes)
    <div class="bg-white rounded-xl shadow">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Program Details</h2>
        </div>
        <div class="px-6 py-5 space-y-4 text-sm">
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
            @if($team->session_times)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Times</span>
                <span class="text-gray-800">{{ $team->session_times }}</span>
            </div>
            @endif
            @if($team->rules_url)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Rules</span>
                <a href="{{ $team->rules_url }}" target="_blank" rel="noopener"
                   class="text-blue-600 hover:underline">View Rules →</a>
            </div>
            @endif
            @if($team->schedule_url)
            <div class="flex gap-3">
                <span class="text-gray-400 w-28 shrink-0">Schedule</span>
                <a href="{{ $team->schedule_url }}" target="_blank" rel="noopener"
                   class="text-blue-600 hover:underline">View Schedule →</a>
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
            <div class="px-6 py-8 text-center text-gray-400">
                <p>Roster not yet published.</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach($team->players as $player)
                <li class="px-6 py-3 flex items-center justify-between">
                    <span class="text-gray-800">{{ $player->name }}</span>
                    @if($player->jersey_number)
                        <span class="text-sm text-gray-400">#{{ $player->jersey_number }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>

@endsection
