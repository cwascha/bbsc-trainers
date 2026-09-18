<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Hours</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(auth()->user()->is_lead_trainer)
            {{-- Planning hours entry for lead trainer --}}
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-5">
                <h3 class="font-semibold text-amber-800 mb-1">Planning Hours</h3>
                <p class="text-sm text-amber-700 mb-4">
                    Enter your planning hours for the current pay period
                    ({{ \Carbon\Carbon::parse($currentPeriodStart)->format('M j') }}
                    – {{ \Carbon\Carbon::parse($currentPeriodStart)->addDays(13)->format('M j, Y') }}).
                    Session hours are counted automatically.
                </p>
                @if(session('success'))
                    <div class="mb-3 text-sm text-green-700 font-medium">{{ session('success') }}</div>
                @endif
                <form method="POST" action="{{ route('hours.planning.update') }}" class="flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="period_start" value="{{ $currentPeriodStart }}">
                    <label class="text-sm font-medium text-amber-800">Planning hours this period:</label>
                    <input type="number" name="planning_hours" value="{{ $currentPlanningHours }}"
                           step="0.25" min="0" max="999" required
                           class="w-20 border-amber-300 rounded-md shadow-sm text-sm focus:ring-amber-400 focus:border-amber-400">
                    <button type="submit"
                            class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-md hover:bg-amber-700">
                        Save
                    </button>
                </form>
            </div>
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-sm text-gray-500">Total Sessions Worked</p>
                    <p class="text-4xl font-bold text-gray-800 mt-1">{{ $workedSessions->count() }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-sm text-gray-500">Total Hours Worked</p>
                    <p class="text-4xl font-bold text-blue-600 mt-1">{{ $totalHours }}</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="font-semibold text-gray-800">Session History</h3>
                </div>
                @if($workedSessions->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-500">
                        No sessions worked yet this season.
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Day</th>
                                <th class="px-6 py-3 text-left">Weekend</th>
                                <th class="px-6 py-3 text-left">Hours</th>
                                <th class="px-6 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($workedSessions as $av)
                            <tr>
                                <td class="px-6 py-3 font-medium text-gray-800">{{ $av->trainingDay->formattedDate }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $av->trainingDay->dayName }}</td>
                                <td class="px-6 py-3 text-gray-600">Weekend {{ $av->trainingDay->weekend_number }}</td>
                                <td class="px-6 py-3 text-gray-600">7</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $av->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ ucfirst($av->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold text-gray-700">
                            <tr>
                                <td colspan="3" class="px-6 py-3">Total</td>
                                <td class="px-6 py-3">{{ $totalHours }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
