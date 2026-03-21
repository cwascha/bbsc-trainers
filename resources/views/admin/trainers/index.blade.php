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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Add Individual Trainer --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Add Trainer</h2>
            <form method="POST" action="{{ route('admin.trainers.store') }}" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">First Name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                               class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                               placeholder="Jane">
                        @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                               class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                               placeholder="Smith">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                           placeholder="jane@example.com">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Phone <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="tel" name="phone" value="{{ old('phone') }}"
                           class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                           placeholder="555-867-5309">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Venmo <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="text" name="venmo" value="{{ old('venmo') }}"
                           class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                           placeholder="@username">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pay Rate ($/hr) <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="number" name="pay_rate" value="{{ old('pay_rate') }}" step="0.01" min="0"
                           class="block w-full rounded border-gray-300 text-sm focus:ring-gray-500 focus:border-gray-500"
                           placeholder="e.g. 18.00">
                </div>
                <div class="pt-1">
                    <button type="submit"
                            class="w-full px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                        Add Trainer
                    </button>
                </div>
                <p class="text-xs text-gray-400">The trainer will use "Forgot Password" to set their password.</p>
            </form>
        </div>

        {{-- CSV Import --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-gray-800 mb-1">Import from CSV</h2>
            <p class="text-sm text-gray-500 mb-4">
                Upload a CSV with columns: <strong>Email</strong> (required) plus any of
                <strong>First Name</strong>, <strong>Last Name</strong>,
                <strong>Phone</strong>, <strong>Venmo</strong>, <strong>Pay Rate</strong>.
                <br>
                New trainers are created. Existing trainers have their <strong>Pay Rate</strong>, <strong>Venmo</strong>,
                and <strong>Phone</strong> updated if those columns are present — other fields are left unchanged.
                <br><span class="text-xs text-gray-400 mt-1 block">Tip: To bulk-set pay rates, use a CSV with just Email + Pay Rate columns.</span>
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

    </div>

    {{-- Trainer Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    <th class="px-6 py-3 text-left">Phone</th>
                    <th class="px-6 py-3 text-left">Venmo</th>
                    <th class="px-6 py-3 text-left">Pay Rate</th>
                    <th class="px-6 py-3 text-left">Sessions</th>
                    <th class="px-6 py-3 text-left">Hours</th>
                    <th class="px-6 py-3 text-left">W9</th>
                    <th class="px-6 py-3 text-left">Registered</th>
                    <th class="px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($trainers as $trainer)
                <tr>
                    <td class="px-6 py-3 font-medium text-gray-800">{{ $trainer->name }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->email }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->phone ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->venmo ?? '—' }}</td>
                    <td class="px-6 py-3 text-gray-600">
                        @if($trainer->pay_rate)
                            ${{ number_format($trainer->pay_rate, 2) }}/hr
                        @else
                            <span class="text-yellow-500 text-xs">Not set</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->sessions_worked }}</td>
                    <td class="px-6 py-3 text-gray-600">{{ $trainer->sessions_worked * 7 }}</td>
                    <td class="px-6 py-3">
                        @if($trainer->w9_received_at)
                            {{-- Received: show badge + download if available + unmark --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-medium whitespace-nowrap">
                                    ✓ Received {{ $trainer->w9_received_at->format('M j') }}
                                </span>
                                @if($trainer->w9_uploaded_at)
                                    <a href="{{ route('admin.trainers.w9.download', $trainer) }}"
                                       class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-medium whitespace-nowrap hover:bg-blue-200">
                                        ↓ View
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.trainers.w9.received', $trainer) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-gray-400 hover:text-red-500 whitespace-nowrap" title="Unmark received">
                                        Unmark
                                    </button>
                                </form>
                            </div>
                        @else
                            {{-- Not received yet: show upload status + Mark Received button --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($trainer->w9_uploaded_at)
                                    <a href="{{ route('admin.trainers.w9.download', $trainer) }}"
                                       class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-medium whitespace-nowrap hover:bg-blue-200">
                                        ↓ View W9
                                    </a>
                                @else
                                    <span class="text-xs text-red-400 font-medium whitespace-nowrap">Not uploaded</span>
                                @endif
                                <form method="POST" action="{{ route('admin.trainers.w9.received', $trainer) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-2 py-0.5 bg-gray-100 text-gray-700 rounded text-xs hover:bg-green-100 hover:text-green-700 whitespace-nowrap">
                                        Mark Received
                                    </button>
                                </form>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-gray-500">{{ $trainer->created_at->format('M j, Y') }}</td>
                    <td class="px-6 py-3">
                        <form method="POST" action="{{ route('admin.trainers.destroy', $trainer) }}"
                              onsubmit="return confirm('Remove {{ addslashes($trainer->name) }}? This will also delete all their sign-ups and assignments.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Remove</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="px-6 py-8 text-center text-gray-400">No trainers registered yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
