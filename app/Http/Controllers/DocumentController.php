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

    public function view(Document $document)
    {
        if (! Storage::exists($document->file_path)) {
            abort(404, 'Document not found.');
        }

        $ext = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));

        // PDFs: serve inline so the browser renders them directly
        if ($ext === 'pdf') {
            return response(Storage::get($document->file_path), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
            ]);
        }

        // Office files: redirect to Microsoft Office Online viewer using a temporary signed URL
        $officeTypes = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
        if (in_array($ext, $officeTypes)) {
            $temporaryUrl = Storage::temporaryUrl($document->file_path, now()->addMinutes(15));
            $viewerUrl    = 'https://view.officeapps.live.com/op/view.aspx?src=' . urlencode($temporaryUrl);
            return redirect($viewerUrl);
        }

        // Fallback: download
        return Storage::download($document->file_path, $document->original_filename);
    }
}
