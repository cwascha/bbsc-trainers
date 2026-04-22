@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Training Plans</h1>

    {{-- Upload Form --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Upload Training Plan</h2>
        <form method="POST" action="{{ route('admin.training-plans.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weekend</label>
                    <select name="weekend_number" required class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select weekend...</option>
                        @foreach($weekendNumbers as $num)
                        <option value="{{ $num }}">Weekend {{ $num }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                    <select name="program" required class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="main">K / 1st Grade</option>
                        <option value="sparks">Sparks (Pre-K)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" required placeholder="e.g. Weekend 1 Training Plan"
                           class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File (PDF or Excel)</label>
                    <input type="file" name="file" accept=".pdf,.xlsx,.xls" required
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer focus:outline-none">
                </div>
            </div>
            <p class="text-xs text-gray-400">Uploading a plan for the same weekend &amp; program replaces the existing one. Saturday trainers receive links to both the K/1st and Sparks plans when assigned.</p>
            <div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                    Upload Plan
                </button>
            </div>
        </form>
    </div>

    {{-- Existing Plans --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">Uploaded Plans</h2>
        </div>
        @if($plans->isEmpty())
            <div class="px-6 py-8 text-center text-gray-400">No training plans uploaded yet.</div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 text-left">Weekend</th>
                        <th class="px-6 py-3 text-left">Program</th>
                        <th class="px-6 py-3 text-left">Title</th>
                        <th class="px-6 py-3 text-left">Dates</th>
                        <th class="px-6 py-3 text-left">Uploaded By</th>
                        <th class="px-6 py-3 text-left">Date</th>
                        <th class="px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($plans as $plan)
                    <tr>
                        <td class="px-6 py-3 font-medium">Weekend {{ $plan->weekend_number }}</td>
                        <td class="px-6 py-3">
                            @if($plan->program === 'sparks')
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded text-xs font-medium">Sparks (Pre-K)</span>
                            @else
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded text-xs font-medium">K / 1st Grade</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">{{ $plan->title }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $plan->weekendDates }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $plan->uploader->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $plan->created_at->format('M j, Y') }}</td>
                        <td class="px-6 py-3">
                            <div class="flex space-x-3">
                                <a href="{{ route('training-plans.download', $plan->id) }}" class="text-blue-600 hover:underline">Download</a>
                                <form method="POST" action="{{ route('admin.training-plans.destroy', $plan->id) }}"
                                      onsubmit="return confirm('Delete this plan?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
