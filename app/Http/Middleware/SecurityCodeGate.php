<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityCodeGate
{
    /**
     * Valid security codes for site access.
     */
    protected array $validCodes = ['1000', '2000', '3000', '4000'];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if already verified
        if ($request->session()->get('security_code_verified')) {
            return $next($request);
        }

        // Skip if this is the security code page itself
        if ($request->routeIs('security.code') || $request->routeIs('security.verify')) {
            return $next($request);
        }

        // Redirect to security code page
        return redirect()->route('security.code');
    }

    /**
     * Check if a code is valid.
     */
    public static function isValidCode(string $code): bool
    {
        return in_array($code, ['1000', '2000', '3000', '4000']);
    }
}
