<?php

namespace App\Models;

use App\Traits\BelongsToClub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    use BelongsToClub;

    protected $fillable = ['club_id', 'name', 'year', 'status'];

    public function trainingDays(): HasMany
    {
        return $this->hasMany(TrainingDay::class)->orderBy('date')->orderBy('session_start');
    }
}
