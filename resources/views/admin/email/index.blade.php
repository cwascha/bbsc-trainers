@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Email Trainers</h1>
        <a href="{{ route('admin.trainers.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">← Back to Trainers</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.email.send') }}">
            @csrf

            {{-- Recipients --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="font-semibold text-gray-800">Recipients
                        <span class="ml-1 text-sm font-normal text-gray-400" id="selected-count"></span>
                    </label>
                    <div class="flex items-center gap-4 text-sm">
                        <select id="weekend-filter" class="border-gray-300 rounded text-sm focus:ring-gray-500 focus:border-gray-500">
                            <option value="">Select by weekend...</option>
                            @foreach($weekendTrainers->sortKeys() as $weekend => $ids)
                                <option value="{{ $ids->join(',') }}">Weekend {{ $weekend }} ({{ $ids->count() }} assigned)</option>
                            @endforeach
                        </select>
                        <button type="button" id="select-all"
                                class="text-blue-600 hover:underline">Select All</button>
                        <button type="button" id="deselect-all"
                                class="text-gray-400 hover:text-gray-600 hover:underline">Deselect All</button>
                    </div>
                </div>

                @if($trainers->isEmpty())
                    <p class="text-sm text-gray-400 py-4 text-center border rounded-lg">
                        No trainers registered yet.
                    </p>
                @else
                    <div class="border rounded-lg divide-y max-h-72 overflow-y-auto">
                        @foreach($trainers as $trainer)
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="recipients[]" value="{{ $trainer->id }}"
                                   class="trainer-checkbox h-4 w-4 rounded border-gray-300 text-gray-800 focus:ring-gray-500">
                            <span class="text-sm font-medium text-gray-800 w-48 truncate">{{ $trainer->name }}</span>
                            <span class="text-sm text-gray-400">{{ $trainer->email }}</span>
                        </label>
                        @endforeach
                    </div>
                @endif

                @error('recipients')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Subject --}}
            <div class="mb-4">
                <label for="subject" class="block font-semibold text-gray-800 mb-1">Subject</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required
                       class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-gray-500 focus:border-gray-500"
                       placeholder="e.g. Weekend 1 — Training Info">
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Body --}}
            <div class="mb-6">
                <label for="body" class="block font-semibold text-gray-800 mb-1">Message</label>
                <textarea id="body" name="body" rows="12" required
                          class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-gray-500 focus:border-gray-500"
                          placeholder="Write your message here...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-4 border-t">
                <p class="text-sm text-gray-400">
                    Sent from <strong class="text-gray-600">chris@bbscsoccer.com</strong>
                    &middot; Each trainer receives a personalised "Hi [Name]" greeting
                </p>
                <button type="submit"
                        class="px-6 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 transition">
                    Send Email
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const checkboxes = () => document.querySelectorAll('.trainer-checkbox');
    const updateCount = () => {
        const checked = document.querySelectorAll('.trainer-checkbox:checked').length;
        const total   = checkboxes().length;
        document.getElementById('selected-count').textContent =
            checked > 0 ? '(' + checked + ' of ' + total + ' selected)' : '';
    };
    document.getElementById('select-all')?.addEventListener('click', () => {
        checkboxes().forEach(cb => cb.checked = true);
        updateCount();
    });
    document.getElementById('deselect-all')?.addEventListener('click', () => {
        checkboxes().forEach(cb => cb.checked = false);
        updateCount();
    });
    document.getElementById('weekend-filter')?.addEventListener('change', function () {
        const ids = this.value ? this.value.split(',') : [];
        checkboxes().forEach(cb => {
            cb.checked = ids.includes(cb.value);
        });
        updateCount();
        this.value = ''; // reset dropdown so it can be used again
    });
    checkboxes().forEach(cb => cb.addEventListener('change', updateCount));
</script>

@endsection
