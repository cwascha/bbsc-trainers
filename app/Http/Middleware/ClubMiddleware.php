<?php

namespace App\Http\Middleware;

use App\Models\Club;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClubMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $club = $this->resolve($request);

        if (! $club) {
            abort(404, 'Club not found.');
        }

        app()->instance('currentClub', $club);

        // Make club available in all Blade views
        view()->share('currentClub', $club);

        return $next($request);
    }

    private function resolve(Request $request): ?Club
    {
        // Local dev / CI: use CLUB_SLUG env var to bypass subdomain detection
        if ($slug = env('CLUB_SLUG')) {
            return Club::where('slug', $slug)->first();
        }

        // Production: extract subdomain from host (bbsc.pitchside.app → bbsc)
        $host  = $request->getHost();
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            return Club::where('slug', $parts[0])->first();
        }

        // Single-segment host (localhost, IP) — fall back to first club for convenience
        return Club::first();
    }
}
