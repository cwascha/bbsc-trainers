<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Welcome back, {{ Auth::user()->name }}!
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Upcoming Sessions</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $upcomingAssigned->count() }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="bg-green-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Hours Worked</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalHours }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 flex items-center space-x-4">
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Sessions Completed</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalSessions }}</p>
                    </div>
                </div>
            </div>

            {{-- Upcoming Assigned Sessions --}}
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Your Upcoming Assigned Sessions</h3>
                    <a href="{{ route('schedule.index') }}" class="text-sm text-blue-600 hover:underline">Sign up for more →</a>
                </div>
                @if($upcomingAssigned->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-500">
                        <p>You have no upcoming assigned sessions.</p>
                        <a href="{{ route('schedule.index') }}" class="mt-2 inline-block text-blue-600 hover:underline">Browse the schedule and sign up!</a>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($upcomingAssigned as $av)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">{{ $av->trainingDay->formattedDate }}</p>
                                <p class="text-sm text-gray-500">8:30 AM – 3:30 PM · Weekend {{ $av->trainingDay->weekend_number }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                @if($av->status === 'confirmed')
                                    <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">Confirmed</span>
                                @elseif($av->status === 'assigned')
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium">Awaiting Confirmation</span>
                                @endif
                                <form method="POST" action="{{ route('availability.destroy', $av->id) }}"
                                      onsubmit="return confirm('Are you sure you want to cancel this session?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 text-sm">Cancel</button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Season Info --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-6 py-4">
                <h3 class="font-semibold text-blue-800 mb-1">2026 Spring Season</h3>
                <p class="text-sm text-blue-700">Sessions run every Saturday &amp; Sunday from 8:30 AM – 3:30 PM. No sessions on May 23–24 (Memorial Day Weekend).</p>
            </div>

        </div>
    </div>
</x-app-layout>
