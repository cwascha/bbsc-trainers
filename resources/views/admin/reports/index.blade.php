@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Payroll Report</h1>

    {{-- Filter Form --}}
    <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}"
                       class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}"
                       class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Filter
            </button>
            <a href="{{ route('admin.reports.index', ['start_date' => $prevStart, 'end_date' => $prevEnd]) }}"
               class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-sm font-medium whitespace-nowrap">
                ← Prev Period
            </a>
            <a href="{{ route('admin.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export CSV</span>
            </a>
        </form>
        <p class="mt-3 text-xs text-gray-400">
            Defaults to the current 2-week pay period. Use "← Prev Period" to view the previous period.
            Set a trainer's pay rate under <a href="{{ route('admin.trainers.index') }}" class="underline">Trainers</a>.
        </p>
    </div>

    {{-- Report Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <p class="text-sm text-gray-600">
                Pay period: <strong>{{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }}</strong>
                – <strong>{{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</strong>
            </p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Venmo</th>
                    <th class="px-6 py-3 text-right">Pay Rate</th>
                    <th class="px-6 py-3 text-right">Sessions</th>
                    <th class="px-6 py-3 text-right">Hours</th>
                    <th class="px-6 py-3 text-right">Total Pay</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php
                    $totalSessions = 0;
                    $totalHours    = 0;
                    $totalPay      = 0;
                @endphp
                @forelse($trainers->where('sessions_count', '>', 0) as $trainer)
                @php
                    $hours  = $trainer->hours_worked;
                    $pay    = $trainer->pay_rate ? round($hours * $trainer->pay_rate, 2) : null;
                    $totalSessions += $trainer->sessions_count;
                    $totalHours    += $hours;
                    $totalPay      += $pay ?? 0;
                @endphp
                <tr>
                    <td class="px-6 py-3 font-medium text-gray-800">
                        {{ $trainer->name }}
                        <div class="text-xs text-gray-400 font-normal">{{ $trainer->email }}</div>
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->venmo ?? '—' }}</td>
                    <td class="px-6 py-3 text-right text-gray-600">
                        @if($trainer->pay_rate)
                            ${{ number_format($trainer->pay_rate, 2) }}/hr
                        @else
                            <span class="text-yellow-500 text-xs font-medium">Not set</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right text-gray-600">{{ $trainer->sessions_count }}</td>
                    <td class="px-6 py-3 text-right text-gray-600">{{ $hours }}</td>
                    <td class="px-6 py-3 text-right font-semibold text-gray-800">
                        @if($pay !== null)
                            ${{ number_format($pay, 2) }}
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">No sessions found for this pay period.</td>
                </tr>
                @endforelse
            </tbody>
            @if($trainers->where('sessions_count', '>', 0)->isNotEmpty())
            <tfoot class="bg-gray-50 font-semibold text-gray-800">
                <tr>
                    <td colspan="3" class="px-6 py-3">Totals</td>
                    <td class="px-6 py-3 text-right">{{ $totalSessions }}</td>
                    <td class="px-6 py-3 text-right">{{ $totalHours }}</td>
                    <td class="px-6 py-3 text-right text-green-700">${{ number_format($totalPay, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
