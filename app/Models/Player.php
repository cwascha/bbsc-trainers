<?php

namespace App\Models;

use App\Traits\BelongsToClub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    use BelongsToClub;
    protected $fillable = ['team_id', 'first_name', 'last_name'];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
