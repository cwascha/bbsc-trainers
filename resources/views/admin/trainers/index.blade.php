@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Trainers</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-left">Sessions Worked</th>
                    <th class="px-6 py-3 text-left">Hours Worked</th>
                    <th class="px-6 py-3 text-left">Registered</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($trainers as $trainer)
                <tr>
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $trainer->name }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->email }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->phone ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->sessions_worked }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->sessions_worked * 7 }}</td>
                    <td class="px-6 py-3 text-gray-500">{{ $trainer->created_at->format('M j, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-400">No trainers registered yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
