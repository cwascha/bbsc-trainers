@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Email Trainers</h1>

    <form method="POST" action="{{ route('admin.email.send') }}">
        @csrf

        {{-- Compose --}}
        <div class="bg-white rounded-lg shadow p-6 space-y-4">
            <h2 class="font-semibold text-gray-800">Compose</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="255"
                       class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"
                       placeholder="e.g. Weekend 1 Training Reminder">
                @error('subject')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea name="body" required rows="10" maxlength="10000"
                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-400"
                          placeholder="Write your message here...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-xs text-gray-400">Sent from <strong>chris@bbscsoccer.com</strong> · BBSC</p>
        </div>

        {{-- Recipients --}}
        <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">
                    Recipients
                    <span class="ml-2 text-sm font-normal text-gray-500" id="selected-count"></span>
                </h2>
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" id="select-all" class="rounded">
                    Select All
                </label>
            </div>

            @if($trainers->isEmpty())
                <p class="px-6 py-8 text-center text-gray-400 text-sm">No trainers registered yet.</p>
            @else
                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    @foreach($trainers as $trainer)
                    <label class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="recipients[]" value="{{ $trainer->id }}"
                               class="recipient-checkbox rounded">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800">{{ $trainer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $trainer->email }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            @endif

            @error('recipients')
                <p class="px-6 py-3 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit"
                    class="px-6 py-2 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700">
                Send Email
            </button>
        </div>
    </form>
</div>

<script>
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.recipient-checkbox');
    const countEl = document.getElementById('selected-count');

    function updateCount() {
        const n = document.querySelectorAll('.recipient-checkbox:checked').length;
        countEl.textContent = n > 0 ? `(${n} selected)` : '';
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateCount();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', () => {
        selectAll.checked = [...checkboxes].every(c => c.checked);
        updateCount();
    }));
</script>
@endsection
