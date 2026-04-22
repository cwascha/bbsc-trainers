<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollHoursOverride extends Model
{
    protected $fillable = ['user_id', 'period_start', 'hours'];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'hours'        => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
