@extends('layouts.public')

@section('title', 'BBSC Teams — Fall 2026')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Fall 2026 Teams</h1>
    <p class="mt-1 text-gray-500">Rosters and program details for the BBSC Fall 2026 season.</p>
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
