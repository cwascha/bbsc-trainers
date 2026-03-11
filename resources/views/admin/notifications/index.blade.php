@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">SMS Notification Log</h1>

    {{-- Manual Send --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-gray-800 mb-3">Send Notifications Manually</h2>
        <form method="POST" action="{{ route('admin.notifications.send') }}" class="flex items-end space-x-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Training Day</label>
                <select name="training_day_id" required class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select day...</option>
                    @foreach(\App\Models\TrainingDay::where('date', '>=', now()->toDateString())->orderBy('date')->get() as $day)
                    <option value="{{ $day->id }}">{{ $day->formattedDate }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium"
                    onclick="return confirm('Send SMS to all assigned trainers for this day?')">
                Send SMS
            </button>
        </form>
    </div>

    {{-- Logs --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">Notification History</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Sent At</th>
                    <th class="px-6 py-3 text-left">Trainer</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-left">Session</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Message</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($logs as $log)
                <tr>
                    <td class="px-6 py-3 text-gray-500">{{ $log->sent_at->format('M j, Y g:ia') }}</td>
                    <td class="px-6 py-3 font-medium">{{ $log->user->name }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $log->phone }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $log->trainingDay->formattedDate }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-0.5 rounded text-xs {{ $log->status === 'sent' || $log->status === 'queued' ? 'bg-green-100 text-green-700' : ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $log->status }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-gray-500 max-w-xs truncate">{{ $log->message }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">No notifications sent yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
