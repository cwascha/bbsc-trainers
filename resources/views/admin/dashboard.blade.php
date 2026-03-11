@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Admin Overview</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Total Trainers</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalTrainers }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Upcoming Sessions</p>
            <p class="text-3xl font-bold text-gray-800">{{ $upcomingDays->count() }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm text-gray-500">Season</p>
            <p class="text-xl font-bold text-gray-800">Apr 11 – Jun 7, 2026</p>
        </div>
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
                    <th class="px-6 py-3 text-left">Pending</th>
                    <th class="px-6 py-3 text-left">Spots Left</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($upcomingDays as $day)
                <tr>
                    <td class="px-6 py-3 font-medium">{{ $day->formattedDate }}</td>
                    <td class="px-6 py-3 text-gray-600">Weekend {{ $day->weekend_number }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs">{{ $day->assigned_count }}</span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded text-xs">{{ $day->pending_count }}</span>
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
