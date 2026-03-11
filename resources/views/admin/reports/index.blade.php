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
            <a href="{{ route('admin.reports.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
               class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm font-medium flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Export CSV</span>
            </a>
        </form>
    </div>

    {{-- Report Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <p class="text-sm text-gray-600">
                Showing sessions from <strong>{{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }}</strong>
                to <strong>{{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</strong>
            </p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-right">Sessions</th>
                    <th class="px-6 py-3 text-right">Hours</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $totalSessions = 0; $totalHours = 0; @endphp
                @forelse($trainers as $trainer)
                @php $totalSessions += $trainer->sessions_count; $totalHours += $trainer->sessions_count * 7; @endphp
                <tr>
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $trainer->name }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->email }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->phone ?? '—' }}</td>
                    <td class="px-6 py-3 text-right text-gray-600">{{ $trainer->sessions_count }}</td>
                    <td class="px-6 py-3 text-right font-semibold text-gray-800">{{ $trainer->sessions_count * 7 }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">No sessions found for this period.</td>
                </tr>
                @endforelse
            </tbody>
            @if($trainers->isNotEmpty())
            <tfoot class="bg-gray-50 font-semibold text-gray-800">
                <tr>
                    <td colspan="3" class="px-6 py-3">Totals</td>
                    <td class="px-6 py-3 text-right">{{ $totalSessions }}</td>
                    <td class="px-6 py-3 text-right">{{ $totalHours }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
