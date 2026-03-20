@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Trainers</h1>
        <a href="{{ route('admin.email.index') }}"
           class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
            Email Trainers
        </a>
    </div>

    {{-- CSV Import --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-gray-800 mb-1">Import Trainers from CSV</h2>
        <p class="text-sm text-gray-500 mb-4">
            Upload a CSV file with columns: <strong>First Name</strong>, <strong>Last Name</strong>,
            <strong>Phone</strong>, <strong>Email</strong>. Existing accounts (matched by email) will be skipped.
            Imported trainers can use "Forgot Password" to set their password.
            <br>
            <span class="text-xs text-gray-400">Tip: Export as CSV from Excel or Google Sheets before uploading.</span>
        </p>
        <form method="POST" action="{{ route('admin.trainers.import') }}" enctype="multipart/form-data"
              class="flex items-center gap-3">
            @csrf
            <input type="file" name="file" accept=".csv,.txt" required
                   class="block text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
            <button type="submit"
                    class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700 whitespace-nowrap">
                Import
            </button>
        </form>
    </div>

    {{-- Trainer Table --}}
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
