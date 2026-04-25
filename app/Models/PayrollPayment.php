<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPayment extends Model
{
    protected $fillable = ['user_id', 'period_start', 'paid_at'];

    protected $casts = ['paid_at' => 'datetime'];
}
