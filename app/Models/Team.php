<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'name', 'group_name', 'program', 'coach_name',
        'format', 'location', 'session_times', 'rules_url', 'schedule_url', 'notes',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('last_name')->orderBy('first_name');
    }
}
