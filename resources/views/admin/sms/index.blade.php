@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Send SMS</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.sms.send') }}">
            @csrf

            {{-- Recipients --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="font-semibold text-gray-800">Recipients
                        <span class="ml-1 text-sm font-normal text-gray-400" id="selected-count"></span>
                    </label>
                    <div class="flex gap-4 text-sm">
                        <button type="button" id="select-all" class="text-blue-600 hover:underline">Select All</button>
                        <button type="button" id="deselect-all" class="text-gray-400 hover:text-gray-600 hover:underline">Deselect All</button>
                    </div>
                </div>

                @if($trainers->isEmpty())
                    <p class="text-sm text-gray-400 py-4 text-center border rounded-lg">No trainers registered yet.</p>
                @else
                    <div class="border rounded-lg divide-y max-h-72 overflow-y-auto">
                        @foreach($trainers as $trainer)
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer {{ !$trainer->phone ? 'opacity-50' : '' }}">
                            <input type="checkbox" name="recipients[]" value="{{ $trainer->id }}"
                                   class="trainer-checkbox h-4 w-4 rounded border-gray-300 text-gray-800 focus:ring-gray-500"
                                   {{ !$trainer->phone ? 'disabled' : '' }}>
                            <span class="text-sm font-medium text-gray-800 w-40 truncate">{{ $trainer->name }}</span>
                            @if($trainer->phone)
                                <span class="text-sm text-gray-400">{{ $trainer->phone }}</span>
                            @else
                                <span class="text-xs text-red-400 italic">No phone on file</span>
                            @endif
                        </label>
                        @endforeach
                    </div>
                @endif

                @error('recipients')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-1">
                    <label for="message" class="block font-semibold text-gray-800">Message</label>
                    <span class="text-xs text-gray-400"><span id="char-count">0</span> / 160 chars
                        <span id="segment-note" class="hidden text-yellow-600 ml-1">(multiple SMS segments)</span>
                    </span>
                </div>
                <textarea id="message" name="message" rows="5" required maxlength="1600"
                          class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:ring-gray-500 focus:border-gray-500"
                          placeholder="Type your message here...">{{ old('message') }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-4 border-t">
                <p class="text-sm text-gray-400">Sent from your Twilio number · Replies are handled via the SMS webhook</p>
                <button type="submit" class="px-6 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 transition">
                    Send SMS
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const checkboxes = () => document.querySelectorAll('.trainer-checkbox:not(:disabled)');

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

    checkboxes().forEach(cb => cb.addEventListener('change', updateCount));

    // Character counter
    const msgArea = document.getElementById('message');
    const charCount = document.getElementById('char-count');
    const segmentNote = document.getElementById('segment-note');
    msgArea?.addEventListener('input', () => {
        const len = msgArea.value.length;
        charCount.textContent = len;
        segmentNote.classList.toggle('hidden', len <= 160);
    });
</script>

@endsection
