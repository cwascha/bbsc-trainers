<?php

namespace App\Traits;

use App\Models\Club;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToClub
{
    protected static function bootBelongsToClub(): void
    {
        // Automatically scope all queries to the current club
        static::addGlobalScope('club', function (Builder $builder) {
            if ($club = currentClub()) {
                $builder->where($builder->getModel()->getTable() . '.club_id', $club->id);
            }
        });

        // Automatically stamp club_id on new records
        static::creating(function ($model) {
            if (empty($model->club_id) && $club = currentClub()) {
                $model->club_id = $club->id;
            }
        });
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
