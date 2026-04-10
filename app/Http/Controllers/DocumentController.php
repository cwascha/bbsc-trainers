<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::latest()->get();
        return view('documents.index', compact('documents'));
    }

    public function download(Document $document)
    {
        if (! Storage::exists($document->file_path)) {
            abort(404, 'Document not found.');
        }

        return Storage::download($document->file_path, $document->original_filename);
    }
}
