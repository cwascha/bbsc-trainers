<?php

namespace App\Models;

use App\Traits\BelongsToClub;
use Illuminate\Database\Eloquent\Model;

class PayrollPayment extends Model
{
    use BelongsToClub;
    protected $fillable = ['user_id', 'period_start', 'paid_at'];

    protected $casts = ['paid_at' => 'datetime'];
}
