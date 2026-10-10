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

    // Legacy custom domains that predate subdomain routing.
    // Maps full hostname → club slug.
    private const LEGACY_DOMAINS = [
        'trainers.bbscsoccer.com' => 'bbsc',
    ];

    private function resolve(Request $request): ?Club
    {
        // Super-admin impersonation takes highest priority
        if ($clubId = session('impersonating_club_id')) {
            return Club::find($clubId);
        }

        // Local dev / CI: use CLUB_SLUG env var to bypass subdomain detection
        if ($slug = config('app.club_slug')) {
            return Club::where('slug', $slug)->first();
        }

        $host = $request->getHost();

        // Legacy custom domains (e.g. trainers.bbscsoccer.com → bbsc)
        if ($slug = self::LEGACY_DOMAINS[$host] ?? null) {
            return Club::where('slug', $slug)->first();
        }

        // Production: extract subdomain from host (bbsc.trainersync.co → bbsc)
        $parts = explode('.', $host);

        if (count($parts) >= 3) {
            // Ignore www
            $sub = $parts[0] === 'www' ? ($parts[1] ?? null) : $parts[0];
            return $sub ? Club::where('slug', $sub)->first() : null;
        }

        // Single-segment host (localhost, IP) — fall back to first club for convenience
        return Club::first();
    }
}
