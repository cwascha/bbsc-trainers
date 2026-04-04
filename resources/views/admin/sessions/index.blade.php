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
        <div class="px-6 py-3">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Pending (signed up, not yet assigned)</p>
            <div class="flex flex-wrap gap-2">
                @foreach($pendingAvs->sortBy('signed_up_at') as $av)
                <div class="flex items-center space-x-1 bg-yellow-50 border border-yellow-200 rounded px-2 py-1 text-xs text-yellow-800">
                    <span>{{ $av->user->name }}</span>
                    <span class="text-yellow-600">{{ $av->signed_up_at->format('M j g:ia') }}</span>
                    <form method="POST" action="{{ route('admin.availabilities.destroy', $av) }}"
                          onsubmit="return confirm('Remove {{ addslashes($av->user->name) }} from this session?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ml-1 text-yellow-400 hover:text-red-500 leading-none" title="Remove">×</button>
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
        @php $signedUpIds = $day->availabilities->whereNotIn('status', ['cancelled'])->pluck('user_id'); @endphp
        @php $available = $trainers->whereNotIn('id', $signedUpIds); @endphp
        @if($available->isNotEmpty())
        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50">
            <form method="POST" action="{{ route('admin.sessions.add-trainer', $day) }}" class="flex items-center gap-2">
                @csrf
                <select name="user_id" required class="text-sm border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Add trainer —</option>
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
</div>
@endsection
