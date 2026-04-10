<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = ['title', 'description', 'file_path', 'original_filename', 'uploaded_by'];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function fileIcon(): string
    {
        $ext = strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
        return match($ext) {
            'pdf'        => '📄',
            'doc', 'docx' => '📝',
            'xls', 'xlsx' => '📊',
            'ppt', 'pptx' => '📋',
            default       => '📁',
        };
    }
}
