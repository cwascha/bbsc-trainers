@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Sessions & Assignments</h1>
        <form method="POST" action="{{ route('admin.assignments.run') }}">
            @csrf
            <button class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600 text-sm font-medium">
                Run Assignment for Upcoming Weekend
            </button>
        </form>
    </div>

    @if($days->isEmpty())
    <div class="bg-white rounded-lg shadow px-6 py-8 text-center text-gray-400">No upcoming sessions scheduled.</div>
    @endif

    @foreach($days as $day)
    @php
        $assignedAvs = $day->availabilities->whereIn('status', ['assigned', 'confirmed']);
        $pendingAvs  = $day->availabilities->where('status', 'pending');
    @endphp
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">{{ $day->formattedDate }}
                    <span class="text-sm font-normal text-gray-500 ml-2">Weekend {{ $day->weekend_number }}</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">{{ $assignedAvs->count() }}/{{ $day->max_spots }} assigned · {{ $pendingAvs->count() }} pending</p>
            </div>
            <form method="POST" action="{{ route('admin.assignments.run') }}">
                @csrf
                <input type="hidden" name="training_day_id" value="{{ $day->id }}">
                <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                    Assign Now
                </button>
            </form>
        </div>

        @if($assignedAvs->isNotEmpty())
        <div class="px-6 py-3 border-b border-gray-100">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Assigned</p>
            <div class="flex flex-wrap gap-2">
                @foreach($assignedAvs as $av)
                <div class="flex items-center space-x-1 bg-green-50 border border-green-200 rounded px-2 py-1 text-xs">
                    <span class="font-medium text-green-800">{{ $av->user->name }}</span>
                    @if($av->status === 'confirmed')
                        <span class="text-green-600">✓</span>
                    @endif
                    <form method="POST" action="{{ route('admin.availabilities.destroy', $av) }}"
                          onsubmit="return confirm('Remove {{ addslashes($av->user->name) }} from this session?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ml-1 text-gray-400 hover:text-red-500 leading-none" title="Remove">×</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($pendingAvs->isNotEmpty())
        <div class="px-6 py-3 border-b border-gray-100">
            {{-- Assign form defined here — checkboxes reference it via the form="..." attribute --}}
            <form method="POST" action="{{ route('admin.sessions.assign-selected', $day) }}" id="assign-form-{{ $day->id }}">
                @csrf
            </form>

            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold text-gray-500 uppercase">Pending</p>
                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-1 text-xs text-gray-500 cursor-pointer">
                        <input type="checkbox"
                               onchange="document.querySelectorAll('.pending-cb-{{ $day->id }}').forEach(cb => cb.checked = this.checked)">
                        All
                    </label>
                    <button type="submit" form="assign-form-{{ $day->id }}"
                            onclick="if(!document.querySelector('.pending-cb-{{ $day->id }}:checked')){alert('Select at least one trainer.');return false;}"
                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 font-medium">
                        Assign Selected
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($pendingAvs->sortBy('signed_up_at') as $av)
                <div class="flex items-center gap-1 bg-yellow-50 border border-yellow-200 rounded px-2 py-1 text-xs text-yellow-800">
                    <label class="flex items-center gap-1.5 cursor-pointer hover:text-yellow-900">
                        <input type="checkbox" name="availability_ids[]" value="{{ $av->id }}"
                               form="assign-form-{{ $day->id }}"
                               class="pending-cb-{{ $day->id }} rounded border-yellow-400">
                        <span class="font-medium">{{ $av->user->name }}</span>
                        <span class="text-yellow-500">{{ $av->signed_up_at->format('M j g:ia') }}</span>
                    </label>
                    <form method="POST" action="{{ route('admin.availabilities.destroy', $av) }}"
                          onsubmit="return confirm('Remove {{ addslashes($av->user->name) }} from this session?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ml-1 text-gray-300 hover:text-red-500 leading-none" title="Remove">×</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($assignedAvs->isEmpty() && $pendingAvs->isEmpty())
        <div class="px-6 py-4 text-sm text-gray-400">No sign-ups yet.</div>
        @endif

        {{-- Admin: manually add a trainer --}}
        @php $signedUpIds = $day->availabilities->whereIn('status', ['pending', 'assigned', 'confirmed'])->pluck('user_id'); @endphp
        @php $available = $trainers->whereNotIn('id', $signedUpIds); @endphp
        @if($available->isNotEmpty())
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50">
            <form method="POST" action="{{ route('admin.sessions.add-trainer', $day) }}" class="flex items-center gap-2">
                @csrf
                <select name="user_id" required class="text-sm border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Add trainer to pending —</option>
                    @foreach($available as $trainer)
                        <option value="{{ $trainer->id }}">{{ $trainer->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Add</button>
            </form>
        </div>
        @endif
    </div>
    @endforeach

    {{-- Past Sessions --}}
    @if($pastDays->isNotEmpty())
    <div x-data="{ open: false }" class="space-y-4">
        <button @click="open = !open"
                class="flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
            <svg x-bind:class="open ? 'rotate-90' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span x-text="open ? 'Hide Past Sessions' : 'Show Past Sessions ({{ $pastDays->count() }})'"></span>
        </button>

        <div x-show="open" x-cloak class="space-y-4">
            @foreach($pastDays as $day)
            @php
                $worked     = $day->availabilities->whereIn('status', ['assigned', 'confirmed']);
                $declined   = $day->availabilities->where('status', 'declined');
                $cancelled  = $day->availabilities->where('status', 'cancelled');
                $pending    = $day->availabilities->where('status', 'pending');
            @endphp
            <div class="bg-white rounded-lg shadow overflow-hidden opacity-80">
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-700">{{ $day->formattedDate }}
                            <span class="text-sm font-normal text-gray-400 ml-2">Weekend {{ $day->weekend_number }}</span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $worked->count() }} worked
                            @if($declined->count()) · {{ $declined->count() }} declined @endif
                            @if($cancelled->count()) · {{ $cancelled->count() }} cancelled @endif
                            @if($pending->count()) · {{ $pending->count() }} pending @endif
                            · {{ $day->sessionHours() }}h/session
                        </p>
                    </div>
                    <span class="text-xs text-gray-400 italic">Past</span>
                </div>

                @if($worked->isNotEmpty())
                <div class="px-6 py-3 border-b border-gray-100">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Worked</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($worked->sortBy('user.name') as $av)
                        <div class="flex items-center gap-1 bg-green-50 border border-green-200 rounded px-2 py-1 text-xs">
                            <span class="font-medium text-green-800">{{ $av->user->name }}</span>
                            @if($av->status === 'confirmed')
                                <span class="text-green-500" title="Confirmed via SMS">✓</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($declined->isNotEmpty() || $cancelled->isNotEmpty())
                <div class="px-6 py-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Did Not Work</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($declined->merge($cancelled)->sortBy('user.name') as $av)
                        <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded px-2 py-1 text-xs">
                            <span class="text-gray-500">{{ $av->user->name }}</span>
                            <span class="text-gray-300">({{ $av->status }})</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($worked->isEmpty() && $declined->isEmpty() && $cancelled->isEmpty())
                <div class="px-6 py-4 text-sm text-gray-300">No records for this session.</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
