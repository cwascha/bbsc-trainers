<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Club extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'primary_color',
        'accent_color',
        'logo_path',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_price_id',
        'subscription_status',
        'trial_ends_at',
        'roster_notify_phones',
        'timezone',
        'twilio_from',
        'roster_last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at'      => 'datetime',
            'roster_last_sent_at' => 'datetime',
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
        if (in_array($this->subscription_status, ['active', 'trial'])) {
            return true;
        }
        // Trial is active if trial_ends_at hasn't passed yet
        if ($this->subscription_status === 'trial' && $this->trial_ends_at?->isFuture()) {
            return true;
        }
        return false;
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(\App\Models\Season::class);
    }
}
