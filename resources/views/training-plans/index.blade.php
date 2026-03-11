<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Training Plans</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <p class="text-sm text-gray-600">Training plans are uploaded by admin before each weekend. Download your plan to prepare for your session.</p>
                </div>
                @if($plans->isEmpty())
                    <div class="px-6 py-8 text-center text-gray-500">
                        No training plans have been uploaded yet.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($plans as $plan)
                        <div class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-800">{{ $plan->title }}</p>
                                <p class="text-sm text-gray-500">Weekend {{ $plan->weekend_number }} · {{ $plan->weekendDates }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">Uploaded {{ $plan->created_at->format('M j, Y') }}</p>
                            </div>
                            <a href="{{ route('training-plans.download', $plan->id) }}"
                               class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Download PDF</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
