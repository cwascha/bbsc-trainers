@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Admin Overview</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Total Trainers</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalTrainers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Upcoming Sessions</p>
            <p class="text-3xl font-bold text-gray-800">{{ $upcomingDays->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">W9s Received</p>
            <p class="text-3xl font-bold text-gray-800">{{ $w9Received }}</p>
            <p class="text-xs text-gray-400 mt-1">of {{ $totalTrainers }} trainers</p>
        </div>
        <a href="{{ route('admin.trainers.index') }}"
           class="bg-white rounded-lg shadow p-6 block hover:bg-gray-50 transition {{ $w9Missing > 0 ? 'border-l-4 border-red-400' : '' }}">
            <p class="text-sm text-gray-500">W9s Missing</p>
            <p class="text-3xl font-bold {{ $w9Missing > 0 ? 'text-red-500' : 'text-gray-800' }}">{{ $w9Missing }}</p>
            <p class="text-xs text-gray-400 mt-1">click to view trainers</p>
        </a>
    </div>

    {{-- Upcoming Days --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="font-semibold text-gray-800">Upcoming Sessions</h2>
            <a href="{{ route('admin.sessions.index') }}" class="text-sm text-blue-600 hover:underline">Manage →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Weekend</th>
                    <th class="px-6 py-3 text-left">Assigned</th>
                    <th class="px-6 py-3 text-left">Confirmed</th>
                    <th class="px-6 py-3 text-left">Pending</th>
                    <th class="px-6 py-3 text-left">Spots Left</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($upcomingDays as $day)
                <tr class="overflow-visible">
                    <td class="px-6 py-3 font-medium">{{ $day->formattedDate }}</td>
                    <td class="px-6 py-3 text-gray-600">Weekend {{ $day->weekend_number }}</td>
                    @php
                        $assigned  = $day->availabilities->whereIn('status', ['assigned', 'confirmed']);
                        $confirmed = $day->availabilities->where('status', 'confirmed');
                        $pending   = $day->availabilities->where('status', 'pending');
                    @endphp
                    <td class="px-6 py-3">
                        <div class="relative group inline-block">
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs cursor-default">{{ $day->assigned_count }}</span>
                            @if($assigned->isNotEmpty())
                            <div class="absolute z-20 hidden group-hover:block bottom-full left-1/2 -translate-x-1/2 mb-2 w-44 bg-gray-800 text-white text-xs rounded py-2 px-3 shadow-lg">
                                @foreach($assigned as $av)<div>{{ $av->user->name }}</div>@endforeach
                                <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <div class="relative group inline-block">
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs cursor-default">{{ $day->confirmed_count }}</span>
                            @if($confirmed->isNotEmpty())
                            <div class="absolute z-20 hidden group-hover:block bottom-full left-1/2 -translate-x-1/2 mb-2 w-44 bg-gray-800 text-white text-xs rounded py-2 px-3 shadow-lg">
                                @foreach($confirmed as $av)<div>{{ $av->user->name }}</div>@endforeach
                                <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3">
                        <div class="relative group inline-block">
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs cursor-default">{{ $day->pending_count }}</span>
                            @if($pending->isNotEmpty())
                            <div class="absolute z-20 hidden group-hover:block bottom-full left-1/2 -translate-x-1/2 mb-2 w-44 bg-gray-800 text-white text-xs rounded py-2 px-3 shadow-lg">
                                @foreach($pending as $av)<div>{{ $av->user->name }}</div>@endforeach
                                <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></div>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $day->max_spots - $day->assigned_count }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Recent Sign-ups --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">Recent Sign-ups</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($recentSignups as $av)
            <div class="px-6 py-3 flex items-center justify-between text-sm">
                <div>
                    <span class="font-medium">{{ $av->user->name }}</span>
                    <span class="text-gray-500 ml-2">signed up for {{ $av->trainingDay->formattedDate }}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded text-xs
                        {{ $av->status === 'confirmed' ? 'bg-green-100 text-green-700' :
                           ($av->status === 'assigned' ? 'bg-yellow-100 text-yellow-700' :
                           ($av->status === 'cancelled' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600')) }}">
                        {{ ucfirst($av->status) }}
                    </span>
                    <span class="text-gray-400">{{ $av->signed_up_at->diffForHumans() }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
