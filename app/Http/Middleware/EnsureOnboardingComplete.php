<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * Redirects users to onboarding if they haven't completed it.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if ($tenant && !$tenant->onboarding_completed) {
            // Allow access to onboarding routes
            if ($request->routeIs('onboarding*') || $request->is('onboarding*')) {
                return $next($request);
            }

            // Allow logout
            if ($request->routeIs('logout') || $request->is('logout')) {
                return $next($request);
            }

            // Allow collaborator invite acceptance
            if ($request->routeIs('collaborator.accept*') || $request->is('invite/*')) {
                return $next($request);
            }

            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
