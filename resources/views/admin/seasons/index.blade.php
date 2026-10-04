@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Seasons</h1>
    </div>

    {{-- Seasons list --}}
    @if($seasons->isEmpty())
        <div class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
            No seasons yet. Create one below to get started.
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($seasons as $season)
            <div class="bg-white rounded-lg shadow p-5 flex flex-col gap-3">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-bold text-gray-800 text-lg leading-tight">{{ $season->name }}</p>
                        <p class="text-sm text-gray-400 mt-0.5">{{ $season->year }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full
                        @if($season->status === 'active') bg-green-100 text-green-700
                        @elseif($season->status === 'upcoming') bg-blue-100 text-blue-700
                        @else bg-gray-100 text-gray-500
                        @endif">
                        {{ ucfirst($season->status) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500">
                    {{ $season->trainingDays->count() }} session{{ $season->trainingDays->count() !== 1 ? 's' : '' }}
                    @if($season->trainingDays->isNotEmpty())
                        &middot;
                        {{ $season->trainingDays->first()->date->format('M j') }}
                        –
                        {{ $season->trainingDays->last()->date->format('M j, Y') }}
                    @endif
                </p>
                <div class="flex gap-2 mt-auto pt-2 border-t">
                    <a href="{{ route('admin.seasons.show', $season) }}"
                       class="flex-1 text-center px-3 py-1.5 text-sm bg-gray-800 text-white rounded hover:bg-gray-700 transition">
                        Manage Days
                    </a>
                    <form method="POST" action="{{ route('admin.seasons.destroy', $season) }}"
                          onsubmit="return confirm('Delete {{ $season->name }}? Training days will be unlinked but not deleted.')">
                        @csrf @method('DELETE')
                        <button class="px-3 py-1.5 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50 transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    {{-- New season form --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-base font-semibold text-gray-700 mb-4">New Season</h2>
        <form method="POST" action="{{ route('admin.seasons.store') }}" class="flex flex-wrap gap-4 items-end">
            @csrf
            <div class="flex-1 min-w-40">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Spring 2027"
                       class="w-full rounded border-gray-300 text-sm shadow-sm">
                @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="w-28">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Year</label>
                <input type="number" name="year" value="{{ old('year', date('Y')) }}" required min="2020" max="2040"
                       class="w-full rounded border-gray-300 text-sm shadow-sm">
            </div>
            <div class="w-36">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full rounded border-gray-300 text-sm shadow-sm">
                    <option value="upcoming" {{ old('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <button type="submit" class="px-5 py-2 bg-gray-800 text-white text-sm font-semibold rounded hover:bg-gray-700 transition">
                Create Season
            </button>
        </form>
    </div>
</div>

@endsection
