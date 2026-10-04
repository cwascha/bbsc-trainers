@extends('layouts.admin')
@section('content')

@php
    $editId = request()->integer('edit');
    $weekends = $season->trainingDays->groupBy('weekend_number');
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center gap-4 justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.seasons.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                ← Seasons
            </a>
            <h1 class="text-2xl font-bold text-gray-800">{{ $season->name }}</h1>
            <span class="text-xs font-semibold px-2 py-1 rounded-full
                @if($season->status === 'active') bg-green-100 text-green-700
                @elseif($season->status === 'upcoming') bg-blue-100 text-blue-700
                @else bg-gray-100 text-gray-500
                @endif">
                {{ ucfirst($season->status) }}
            </span>
        </div>
        <button onclick="document.getElementById('rename-form').classList.toggle('hidden')"
                class="text-sm text-gray-500 hover:text-gray-700 underline">
            Rename / change status
        </button>
    </div>

    {{-- Rename form --}}
    <form id="rename-form" method="POST" action="{{ route('admin.seasons.update', $season) }}"
          class="hidden bg-white rounded-lg shadow p-4 flex flex-wrap gap-4 items-end">
        @csrf @method('PATCH')
        <div class="flex-1 min-w-36">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Season name</label>
            <input type="text" name="name" value="{{ old('name', $season->name) }}" required
                   class="w-full rounded border-gray-300 text-sm shadow-sm">
        </div>
        <div class="w-28">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Year</label>
            <input type="number" name="year" value="{{ old('year', $season->year) }}" required min="2020" max="2040"
                   class="w-full rounded border-gray-300 text-sm shadow-sm">
        </div>
        <div class="w-36">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
            <select name="status" class="w-full rounded border-gray-300 text-sm shadow-sm">
                @foreach(['upcoming','active','completed'] as $s)
                    <option value="{{ $s }}" {{ old('status', $season->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">Save</button>
    </form>

    {{-- ── Bulk Generate ──────────────────────────────────────────────────── --}}
    <details class="bg-white rounded-lg shadow">
        <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-700 select-none list-none flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Bulk Generate Training Days
        </summary>
        <form method="POST" action="{{ route('admin.seasons.generate', $season) }}" class="p-6 pt-0 space-y-5">
            @csrf
            <p class="text-sm text-gray-500">Generates one Saturday (and optionally a Sunday) per weekend. Existing dates are skipped.</p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">First Saturday</label>
                    <input type="date" name="start_date" required
                           class="w-full rounded border-gray-300 text-sm shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1"># of Weekends</label>
                    <input type="number" name="num_weekends" value="8" required min="1" max="52"
                           class="w-full rounded border-gray-300 text-sm shadow-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Skip Dates <span class="font-normal text-gray-400">(comma-separated, e.g. 2027-05-29)</span></label>
                    <input type="text" name="skip_dates" placeholder="2027-05-29, 2027-05-30"
                           class="w-full rounded border-gray-300 text-sm shadow-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t pt-4">
                {{-- Saturday --}}
                <div class="space-y-3">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Saturday</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Start</label>
                            <input type="time" name="sat_start" value="09:30" required
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">End</label>
                            <input type="time" name="sat_end" value="14:30" required
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Max Spots</label>
                            <input type="number" name="sat_spots" value="12" min="1" max="99" required
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Program <span class="text-gray-400">(optional)</span></label>
                            <input type="text" name="sat_program" placeholder="e.g. Sparks"
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Sunday --}}
                <div class="space-y-3" x-data="{ on: false }">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide flex items-center gap-2">
                        Sunday
                        <label class="font-normal text-gray-400 flex items-center gap-1 cursor-pointer">
                            <input type="checkbox" name="include_sunday" value="1" @change="on = $event.target.checked" class="rounded">
                            include
                        </label>
                    </p>
                    <div class="grid grid-cols-2 gap-3" :class="on ? '' : 'opacity-40 pointer-events-none'">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Start</label>
                            <input type="time" name="sun_start" value="09:30"
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">End</label>
                            <input type="time" name="sun_end" value="12:30"
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3" :class="on ? '' : 'opacity-40 pointer-events-none'">
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Max Spots</label>
                            <input type="number" name="sun_spots" value="12" min="1" max="99"
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Program <span class="text-gray-400">(optional)</span></label>
                            <input type="text" name="sun_program" placeholder="e.g. Kindergarten"
                                   class="w-full rounded border-gray-300 text-sm shadow-sm">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-semibold rounded hover:bg-blue-700 transition">
                    Generate Days
                </button>
            </div>
        </form>
    </details>

    {{-- ── Training Days ────────────────────────────────────────────────────── --}}
    @if($season->trainingDays->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
            No training days yet. Use the bulk generator above or add days individually below.
        </div>
    @else
        @foreach($weekends as $weekendNum => $days)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-2 bg-gray-50 border-b flex items-center justify-between">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Weekend {{ $weekendNum }}</span>
                <span class="text-xs text-gray-400">{{ $days->count() }} session{{ $days->count() !== 1 ? 's' : '' }}</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 text-left">Date</th>
                        <th class="px-4 py-2 text-left">Program</th>
                        <th class="px-4 py-2 text-left">Start</th>
                        <th class="px-4 py-2 text-left">End</th>
                        <th class="px-4 py-2 text-left">Hours</th>
                        <th class="px-4 py-2 text-left">Spots</th>
                        <th class="px-4 py-2 text-left">Assigned</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($days as $day)
                    <tr class="{{ $editId === $day->id ? 'bg-yellow-50' : 'hover:bg-gray-50' }} transition">
                        @if($editId === $day->id)
                        {{-- Inline edit row --}}
                        <td colspan="8" class="px-4 py-3">
                            <form method="POST" action="{{ route('admin.seasons.days.update', [$season, $day]) }}"
                                  class="flex flex-wrap gap-3 items-end">
                                @csrf @method('PATCH')
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Date</label>
                                    <input type="date" name="date" value="{{ $day->date->toDateString() }}" required
                                           class="rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Program</label>
                                    <input type="text" name="program" value="{{ $day->program }}" placeholder="optional"
                                           class="w-32 rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Weekend #</label>
                                    <input type="number" name="weekend_number" value="{{ $day->weekend_number }}" min="1" max="99" required
                                           class="w-20 rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Start</label>
                                    <input type="time" name="session_start" value="{{ substr($day->session_start, 0, 5) }}" required
                                           class="rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">End</label>
                                    <input type="time" name="session_end" value="{{ substr($day->session_end, 0, 5) }}" required
                                           class="rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Max Spots</label>
                                    <input type="number" name="max_spots" value="{{ $day->max_spots }}" min="1" max="99" required
                                           class="w-20 rounded border-gray-300 text-sm shadow-sm">
                                </div>
                                <div class="flex gap-2">
                                    <button class="px-3 py-1.5 bg-gray-800 text-white text-xs rounded hover:bg-gray-700">Save</button>
                                    <a href="{{ route('admin.seasons.show', $season) }}"
                                       class="px-3 py-1.5 border border-gray-300 text-gray-600 text-xs rounded hover:bg-gray-100">Cancel</a>
                                </div>
                            </form>
                        </td>
                        @else
                        <td class="px-4 py-2.5 font-medium text-gray-800">
                            {{ $day->date->format('D, M j, Y') }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $day->program ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ \Carbon\Carbon::parse($day->session_start)->format('g:i A') }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ \Carbon\Carbon::parse($day->session_end)->format('g:i A') }}</td>
                        <td class="px-4 py-2.5 text-gray-500">{{ number_format($day->sessionHours(), 1) }}h</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $day->max_spots }}</td>
                        <td class="px-4 py-2.5 text-gray-600">{{ $day->assignedCount() }} / {{ $day->max_spots }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <a href="{{ route('admin.seasons.show', [$season, 'edit' => $day->id]) }}"
                               class="text-xs text-blue-600 hover:underline mr-3">Edit</a>
                            <form method="POST" action="{{ route('admin.seasons.days.destroy', [$season, $day]) }}"
                                  class="inline" onsubmit="return confirm('Remove this training day?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:underline">Delete</button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    @endif

    {{-- ── Add Single Day ───────────────────────────────────────────────────── --}}
    <details class="bg-white rounded-lg shadow">
        <summary class="px-6 py-4 cursor-pointer font-semibold text-gray-700 select-none list-none flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Single Training Day
        </summary>
        <form method="POST" action="{{ route('admin.seasons.days.store', $season) }}"
              class="p-6 pt-0 flex flex-wrap gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Date</label>
                <input type="date" name="date" required
                       class="rounded border-gray-300 text-sm shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Program <span class="text-gray-400">(optional)</span></label>
                <input type="text" name="program" placeholder="e.g. Sparks"
                       class="w-32 rounded border-gray-300 text-sm shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Weekend #</label>
                <input type="number" name="weekend_number" value="{{ ($season->trainingDays->max('weekend_number') ?? 0) + 1 }}" min="1" max="99" required
                       class="w-20 rounded border-gray-300 text-sm shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Start</label>
                <input type="time" name="session_start" value="09:30" required
                       class="rounded border-gray-300 text-sm shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">End</label>
                <input type="time" name="session_end" value="14:30" required
                       class="rounded border-gray-300 text-sm shadow-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Max Spots</label>
                <input type="number" name="max_spots" value="12" min="1" max="99" required
                       class="w-20 rounded border-gray-300 text-sm shadow-sm">
            </div>
            <button type="submit" class="px-5 py-2 bg-gray-800 text-white text-sm font-semibold rounded hover:bg-gray-700 transition">
                Add Day
            </button>
        </form>
    </details>

</div>

@push('scripts')
<script>
// Auto-open rename form if there's a validation error
@if($errors->any())
document.getElementById('rename-form').classList.remove('hidden');
@endif
</script>
@endpush

@endsection
