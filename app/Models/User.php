<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'phone',
        'role',
        'password',
        'w9_path',
        'w9_uploaded_at',
        'w9_received_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'w9_uploaded_at'    => 'datetime',
            'w9_received_at'    => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    public function trainingPlans(): HasMany
    {
        return $this->hasMany(TrainingPlan::class, 'uploaded_by');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function hoursWorked(): int
    {
        return $this->availabilities()
            ->whereIn('status', ['assigned', 'confirmed'])
            ->whereHas('trainingDay', fn($q) => $q->where('date', '<', now()->toDateString()))
            ->count() * 7;
    }
}
