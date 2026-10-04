<?php

use App\Models\Club;

if (! function_exists('currentClub')) {
    function currentClub(): ?Club
    {
        try {
            return app('currentClub');
        } catch (\Throwable) {
            return null;
        }
    }
}
