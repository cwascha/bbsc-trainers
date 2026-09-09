<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name', 'program', 'coach_name', 'coach_email',
        'format', 'location', 'session_times', 'rules_url', 'schedule_url', 'notes',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('name');
    }

    public function getProgramLabelAttribute(): string
    {
        return match ($this->program) {
            'sparks'       => 'Sparks',
            'kindergarten' => 'Kindergarten',
            '1st_grade'    => '1st Grade',
            default        => ucfirst($this->program),
        };
    }
}
