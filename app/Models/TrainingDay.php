<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrainingDay extends Model
{
    protected $fillable = [
        'date',
        'weekend_number',
        'max_spots',
        'session_start',
        'session_end',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function trainingPlan(): HasOne
    {
        return $this->hasOne(TrainingPlan::class, 'weekend_number', 'weekend_number');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function assignedCount(): int
    {
        return $this->availabilities()
            ->whereIn('status', ['assigned', 'confirmed'])
            ->count();
    }

    public function spotsRemaining(): int
    {
        return $this->max_spots - $this->assignedCount();
    }

    public function getDayNameAttribute(): string
    {
        return $this->date->format('l');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('F j, Y');
    }

    public function isPast(): bool
    {
        return $this->date->isPast();
    }

    // Number of hours for this session, derived from start/end times
    public function sessionHours(): float
    {
        return \Carbon\Carbon::parse($this->session_end)
            ->diffInMinutes(\Carbon\Carbon::parse($this->session_start)) / 60;
    }

    // Formatted time range, e.g. "11:30 AM - 2:30 PM"
    public function getSessionTimeRangeAttribute(): string
    {
        return \Carbon\Carbon::parse($this->session_start)->format('g:i A')
            . ' - '
            . \Carbon\Carbon::parse($this->session_end)->format('g:i A');
    }
}
