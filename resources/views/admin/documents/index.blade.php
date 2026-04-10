@extends('layouts.admin')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-800">Documents</h1>

    {{-- Upload Form --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="font-semibold text-gray-800 mb-4">Upload Document</h2>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded text-sm">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input type="text" name="title" required placeholder="e.g. Coaching Guidelines"
                           class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="description" placeholder="Brief description..."
                           class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-gray-400">(PDF, Word, Excel, PowerPoint)</span></label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                           class="w-full text-sm text-gray-700 border border-gray-300 rounded-md cursor-pointer focus:outline-none">
                    @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm font-medium">
                Upload Document
            </button>
        </form>
    </div>

    {{-- Document List --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="font-semibold text-gray-800">Uploaded Documents</h2>
        </div>
        @if($documents->isEmpty())
            <div class="px-6 py-8 text-center text-gray-400">No documents uploaded yet.</div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">File</th>
                            <th class="px-6 py-3 text-left">Description</th>
                            <th class="px-6 py-3 text-left">Uploaded By</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($documents as $doc)
                        <tr>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">{{ $doc->fileIcon() }}</span>
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $doc->title }}</p>
                                        <p class="text-xs text-gray-400">{{ $doc->original_filename }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $doc->description ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $doc->uploader->name }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $doc->created_at->format('M j, Y') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <a href="{{ route('documents.view', $doc) }}" target="_blank" class="text-gray-600 hover:underline text-sm">View</a>
                                    <a href="{{ route('documents.download', $doc) }}" class="text-blue-600 hover:underline text-sm">Download</a>
                                    <form method="POST" action="{{ route('admin.documents.destroy', $doc) }}"
                                          onsubmit="return confirm('Delete \'{{ addslashes($doc->title) }}\'?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
