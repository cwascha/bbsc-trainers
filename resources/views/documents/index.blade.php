<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Documents</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if($documents->isEmpty())
                <div class="bg-white rounded-lg shadow px-6 py-12 text-center text-gray-400">
                    No documents have been uploaded yet.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($documents as $doc)
                    <div class="bg-white rounded-lg shadow px-6 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-2xl flex-shrink-0">{{ $doc->fileIcon() }}</span>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-800 truncate">{{ $doc->title }}</p>
                                @if($doc->description)
                                    <p class="text-sm text-gray-500">{{ $doc->description }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-0.5">Added {{ $doc->created_at->format('M j, Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('documents.download', $doc) }}"
                           class="flex-shrink-0 px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                            ↓ Download
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
