@extends('layouts.public')

@section('title', 'BBSC Teams — Fall 2026')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Fall 2026 Teams</h1>
    <p class="mt-1 text-gray-500">Rosters, coaches, and program details for the BBSC Fall 2026 season.</p>
</div>

@if($teams->isEmpty())
    <div class="bg-white rounded-xl shadow p-10 text-center text-gray-400">
        <p class="text-lg">Rosters haven't been published yet — check back soon!</p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($teams as $team)
        @php
            $colors = [
                'sparks'       => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'badge' => 'bg-orange-100 text-orange-700'],
                'kindergarten' => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'badge' => 'bg-blue-100 text-blue-700'],
                '1st_grade'    => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'badge' => 'bg-green-100 text-green-700'],
            ];
            $c = $colors[$team->program] ?? ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'badge' => 'bg-gray-100 text-gray-700'];
        @endphp
        <a href="{{ route('teams.public.show', $team) }}"
           class="block {{ $c['bg'] }} border {{ $c['border'] }} rounded-xl p-6 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-3">
                <h2 class="text-xl font-bold text-gray-900">{{ $team->name }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-medium {{ $c['badge'] }}">{{ $team->program_label }}</span>
            </div>

            @if($team->coach_name)
                <p class="text-sm text-gray-700 mb-1">
                    <span class="font-medium">Coach:</span> {{ $team->coach_name }}
                </p>
            @else
                <p class="text-sm text-amber-600 mb-1 italic">Coach TBD</p>
            @endif

            @if($team->session_times)
                <p class="text-sm text-gray-500 mt-2">{{ $team->session_times }}</p>
            @endif

            <p class="mt-3 text-sm text-gray-600 font-medium">{{ $team->players->count() }} players →</p>
        </a>
        @endforeach
    </div>
@endif

@endsection
