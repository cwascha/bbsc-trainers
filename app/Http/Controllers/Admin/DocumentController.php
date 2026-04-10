<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::with('uploader')->latest()->get();
        return view('admin.documents.index', compact('documents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'file'        => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:20480',
        ]);

        $file             = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        $path             = $file->store('documents', config('filesystems.default'));

        if (! $path) {
            return back()->with('error', 'File upload failed. Please try again.');
        }

        Document::create([
            'title'             => $request->title,
            'description'       => $request->description,
            'file_path'         => $path,
            'original_filename' => $originalFilename,
            'uploaded_by'       => $request->user()->id,
        ]);

        return back()->with('success', "'{$request->title}' uploaded successfully.");
    }

    public function destroy(Document $document): RedirectResponse
    {
        try {
            Storage::delete($document->file_path);
        } catch (\Exception $e) {
            Log::warning('Could not delete document file: ' . $e->getMessage());
        }

        $title = $document->title;
        $document->delete();

        return back()->with('success', "'{$title}' deleted.");
    }
}
