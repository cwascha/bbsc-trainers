<?php

namespace App\Concerns;

use Carbon\Carbon;

trait ResolvesPayPeriod
{
    private static string $PAY_PERIOD_ANCHOR = '2026-01-01';

    protected function currentPayPeriod(): array
    {
        $anchor = Carbon::parse(self::$PAY_PERIOD_ANCHOR);
        $today  = Carbon::today();
        $days   = $anchor->diffInDays($today, false);
        $period = (int) floor(max($days, 0) / 14);
        $start  = $anchor->copy()->addDays($period * 14);
        $end    = $start->copy()->addDays(13);
        return [$start->toDateString(), $end->toDateString()];
    }
}
