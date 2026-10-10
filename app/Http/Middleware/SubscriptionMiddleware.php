<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $club = currentClub();

        if ($club && ! $club->isActive()) {
            // Allow super-admins through regardless
            if ($request->user()?->role === 'superadmin') {
                return $next($request);
            }

            return response()->view('subscription.inactive', ['club' => $club], 402);
        }

        return $next($request);
    }
}
