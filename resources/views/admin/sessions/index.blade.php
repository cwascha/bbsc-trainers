@extends('layouts.admin')
@section('content')

{{-- SMS Day Modal --}}
<div x-data="smsDayModal()" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
     @open-sms-day.window="openFor($event.detail)"
     @keydown.escape.window="open = false">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">Text Assigned Trainers</h3>
                <p class="text-xs text-gray-400 mt-0.5" x-text="dateLabel"></p>
            </div>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            {{-- Recipient preview --}}
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Recipients</p>
                <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                    <template x-for="r in recipients" :key="r.name">
                        <span class="px-2 py-0.5 rounded text-xs"
                              :class="r.phone ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-400 line-through'"
                              x-text="r.name + (r.phone ? '' : ' (no phone)')"></span>
                    </template>
                    <template x-if="recipients.length === 0">
                        <span class="text-sm text-gray-400 italic">No assigned trainers.</span>
                    </template>
                </div>
            </div>
            {{-- Message form --}}
            <form method="POST" :action="actionUrl">
                @csrf
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Message</label>
                        <span class="text-xs text-gray-400"><span x-text="charCount"></span> / 160
                            <span x-show="charCount > 160" class="text-yellow-600 ml-1">(multiple segments)</span>
                        </span>
                    </div>
                    <textarea name="message" rows="4" required maxlength="1600"
                              x-model="message"
                              @input="charCount = message.length"
                              class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                              placeholder="Type your message…"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="open = false"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
                    <button type="submit"
                            :disabled="recipients.filter(r => r.phone).length === 0"
                            class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed">
                        Send to <span x-text="recipients.filter(r => r.phone).length"></span> trainer(s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function smsDayModal() {
    return {
        open: false,
        actionUrl: '',
        dateLabel: '',
        recipients: [],
        message: '',
        charCount: 0,
        openFor({ url, dateLabel, recipients }) {
            this.actionUrl  = url;
            this.dateLabel  = dateLabel;
            this.recipients = recipients;
            this.message    = '';
            this.charCount  = 0;
            this.open       = true;
        }
    }
}
</script>

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
            <div class="flex items-center gap-2">
                @if($assignedAvs->isNotEmpty())
                @php
                    $smsRecipients = $assignedAvs->map(fn($av) => ['name' => $av->user->name, 'phone' => $av->user->phone ?? ''])->values();
                @endphp
                <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-sms-day', { detail: { url: {{ json_encode(route('admin.sms.send-to-day', $day)) }}, dateLabel: {{ json_encode($day->formattedDate) }}, recipients: {{ $smsRecipients->toJson() }} } }))"
                        class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">
                    Text Assigned
                </button>
                @endif
                <form method="POST" action="{{ route('admin.assignments.run') }}">
                    @csrf
                    <input type="hidden" name="training_day_id" value="{{ $day->id }}">
                    <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">
                        Assign Now
                    </button>
                </form>
            </div>
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
                    {{-- Bulk remove form — checkboxes reference it via form="..." --}}
                    <form method="POST"
                          action="{{ route('admin.sessions.bulk-remove-worked', $day) }}"
                          id="bulk-remove-form-{{ $day->id }}"
                          onsubmit="return confirm('Move selected trainer(s) to Did Not Work?')">
                        @csrf
                    </form>

                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold text-gray-400 uppercase">Worked</p>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-1 text-xs text-gray-400 cursor-pointer">
                                <input type="checkbox"
                                       onchange="document.querySelectorAll('.worked-cb-{{ $day->id }}').forEach(cb => cb.checked = this.checked)">
                                All
                            </label>
                            <button type="submit"
                                    form="bulk-remove-form-{{ $day->id }}"
                                    onclick="if(!document.querySelector('.worked-cb-{{ $day->id }}:checked')){alert('Select at least one trainer.');return false;}"
                                    class="px-2 py-0.5 bg-red-50 text-red-600 border border-red-200 text-xs rounded hover:bg-red-100 font-medium">
                                Remove Selected
                            </button>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach($worked->sortBy('user.name') as $av)
                        @php $defaultHours = $av->trainingDay->sessionHours(); @endphp
                        <div class="flex items-center gap-1 bg-green-50 border border-green-200 rounded px-2 py-1 text-xs {{ $av->hours_override !== null ? 'border-amber-300 bg-amber-50' : '' }}">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" name="availability_ids[]" value="{{ $av->id }}"
                                       form="bulk-remove-form-{{ $day->id }}"
                                       class="worked-cb-{{ $day->id }} rounded border-green-400">
                                <span class="font-medium {{ $av->hours_override !== null ? 'text-amber-800' : 'text-green-800' }}">{{ $av->user->name }}</span>
                                @if($av->status === 'confirmed')
                                    <span class="{{ $av->hours_override !== null ? 'text-amber-500' : 'text-green-500' }}" title="Confirmed via SMS">✓</span>
                                @endif
                            </label>
                            {{-- Inline hours override --}}
                            <form method="POST" action="{{ route('admin.availabilities.hours.update', $av) }}" class="flex items-center gap-0.5 ml-1">
                                @csrf @method('PATCH')
                                <input type="number" name="hours" value="{{ $av->hours_override ?? $defaultHours }}"
                                       step="0.25" min="0" max="24"
                                       title="{{ $av->hours_override !== null ? 'Overridden (default: '.$defaultHours.'h)' : 'Default hours — edit to override' }}"
                                       class="w-12 text-xs border rounded px-1 py-0.5 text-right {{ $av->hours_override !== null ? 'border-amber-400 bg-amber-100 text-amber-800' : 'border-gray-300 bg-white text-gray-600' }} focus:ring-1 focus:ring-amber-400 focus:border-amber-400">
                                <button type="submit" class="text-gray-300 hover:text-green-600 leading-none" title="Save hours">✓</button>
                            </form>
                            <span class="text-gray-300 mx-0.5">|</span>
                            <form method="POST" action="{{ route('admin.availabilities.destroy', $av) }}"
                                  onsubmit="return confirm('Move {{ addslashes($av->user->name) }} to Did Not Work?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500 leading-none" title="Move to Did Not Work">×</button>
                            </form>
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

                {{-- Add trainer to past session --}}
                @php
                    $workedIds    = $worked->pluck('user_id');
                    $addableToPast = $trainers->whereNotIn('id', $workedIds);
                @endphp
                @if($addableToPast->isNotEmpty())
                <div class="px-6 py-3 border-t border-gray-100 bg-gray-50">
                    <form method="POST" action="{{ route('admin.sessions.add-trainer', $day) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="user_id" required class="text-sm border-gray-300 rounded px-2 py-1 focus:ring-gray-500 focus:border-gray-500">
                            <option value="">— Add trainer to Worked —</option>
                            @foreach($addableToPast as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Add</button>
                    </form>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
