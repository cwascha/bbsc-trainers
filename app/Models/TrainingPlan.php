<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingPlan extends Model
{
    protected $fillable = [
        'weekend_number',
        'program',
        'title',
        'file_path',
        'uploaded_by',
    ];

    // Human-readable program label
    public function getProgramLabelAttribute(): string
    {
        return match($this->program) {
            'sparks' => 'Sparks (Pre-K)',
            default  => 'K / 1st Grade',
        };
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getWeekendDatesAttribute(): string
    {
        $saturday = TrainingDay::where('weekend_number', $this->weekend_number)
            ->orderBy('date')
            ->first();
        $sunday = TrainingDay::where('weekend_number', $this->weekend_number)
            ->orderBy('date', 'desc')
            ->first();

        if (! $saturday) {
            return "Weekend {$this->weekend_number}";
        }

        return $saturday->date->format('M j') . ' – ' . $sunday->date->format('M j, Y');
    }
}
