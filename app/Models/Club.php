<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'primary_color',
        'accent_color',
        'logo_path',
        'stripe_customer_id',
        'subscription_status',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function trainingDays(): HasMany
    {
        return $this->hasMany(TrainingDay::class);
    }

    public function isActive(): bool
    {
        return in_array($this->subscription_status, ['active', 'trial']);
    }
}
