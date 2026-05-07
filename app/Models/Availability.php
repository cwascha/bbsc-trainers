<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    protected $fillable = [
        'user_id',
        'training_day_id',
        'signed_up_at',
        'status',
        'confirmed_at',
        'cancelled_at',
        'hours_override',
    ];

    protected function casts(): array
    {
        return [
            'signed_up_at'   => 'datetime',
            'confirmed_at'   => 'datetime',
            'cancelled_at'   => 'datetime',
            'hours_override' => 'float',
        ];
    }

    /** Hours actually counted for this session (override takes precedence over the day's default). */
    public function hoursWorked(): float
    {
        return $this->hours_override ?? $this->trainingDay->sessionHours();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trainingDay(): BelongsTo
    {
        return $this->belongsTo(TrainingDay::class);
    }

    public function isAssigned(): bool
    {
        return in_array($this->status, ['assigned', 'confirmed']);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['pending', 'assigned', 'confirmed'])
            && ! $this->trainingDay->isPast();
    }
}
