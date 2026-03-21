<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            2026 Spring Season Schedule
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-blue-50 border border-blue-200 rounded-lg px-6 py-4 text-sm text-blue-800">
                Sign up for the days you're available. Assignments are made every Thursday — first priority goes to trainers who haven't worked that weekend yet, then by sign-up time (first come, first served). You'll receive a text confirmation when assigned.
            </div>

            @foreach($weekends as $weekendNum => $days)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-700">
                        Weekend {{ $weekendNum }}
                        <span class="text-gray-500 font-normal text-sm ml-2">
                            {{ $days->first()->date->format('M j') }} – {{ $days->last()->date->format('M j, Y') }}
                        </span>
                    </h3>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($days as $day)
                    @php
                        $myAv      = $myAvailabilities[$day->id] ?? null;
                        $status    = $myAv['status'] ?? null;
                        $avId      = $myAv['id'] ?? null;
                        $isPast    = $day->isPast();
                        $assignedCount = $day->assignedCount();
                        $spotsLeft = $day->spotsRemaining();
                    @endphp
                    <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="font-medium text-gray-800">{{ $day->formattedDate }}</p>
                            <p class="text-sm text-gray-500">{{ $day->session_time_range }}</p>
                            <div class="mt-1 flex items-center space-x-3 text-xs text-gray-500">
                                <span>{{ $assignedCount }}/{{ $day->max_spots }} assigned</span>
                                @if($spotsLeft > 0 && !$isPast)
                                    <span class="text-green-600">{{ $spotsLeft }} spot{{ $spotsLeft != 1 ? 's' : '' }} open</span>
                                @elseif(!$isPast)
                                    <span class="text-red-500">Full</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            @if($isPast)
                                <span class="text-xs text-gray-400 italic">Past</span>
                            @elseif($status === 'assigned')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-sm rounded-full">Assigned — awaiting confirmation</span>
                                <form method="POST" action="{{ route('availability.destroy', $avId) }}"
                                      onsubmit="return confirm('Cancel this session?')">
                                    @csrf @method('DELETE')
                                    <button class="text-sm text-red-500 hover:text-red-700">Cancel</button>
                                </form>
                            @elseif($status === 'confirmed')
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-sm rounded-full">Confirmed</span>
                                <form method="POST" action="{{ route('availability.destroy', $avId) }}"
                                      onsubmit="return confirm('Cancel this session?')">
                                    @csrf @method('DELETE')
                                    <button class="text-sm text-red-500 hover:text-red-700">Cancel</button>
                                </form>
                            @elseif($status === 'pending')
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm rounded-full">Signed Up</span>
                                <form method="POST" action="{{ route('availability.destroy', $avId) }}"
                                      onsubmit="return confirm('Remove your availability for this day?')">
                                    @csrf @method('DELETE')
                                    <button class="text-sm text-red-500 hover:text-red-700">Remove</button>
                                </form>
                            @elseif($status === 'cancelled' || $status === 'declined')
                                <span class="px-3 py-1 bg-gray-100 text-gray-500 text-sm rounded-full">Cancelled</span>
                                <form method="POST" action="{{ route('availability.store') }}">
                                    @csrf
                                    <input type="hidden" name="training_day_id" value="{{ $day->id }}">
                                    <button class="px-4 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Re-sign Up</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('availability.store') }}">
                                    @csrf
                                    <input type="hidden" name="training_day_id" value="{{ $day->id }}">
                                    <button class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 {{ $spotsLeft <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            {{ $spotsLeft <= 0 ? 'disabled' : '' }}>
                                        Sign Up
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
