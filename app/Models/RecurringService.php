<?php

namespace App\Models;

use App\Traits\BelongsToClub;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringService extends Model
{
    use BelongsToClub;
    protected $fillable = ['user_id', 'description', 'weekly_amount', 'active'];

    protected $casts = ['active' => 'boolean', 'weekly_amount' => 'float'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
