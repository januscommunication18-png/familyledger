<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to protect forms from spam bots using honeypot technique.
 * Bots typically fill all form fields, so a hidden field that should
 * remain empty indicates bot activity.
 */
class HoneypotProtection
{
    /**
     * The honeypot field name.
     */
    protected string $honeypotField = 'website_url_hp';

    /**
     * The timestamp field name for timing-based protection.
     */
    protected string $timestampField = 'form_time_hp';

    /**
     * Minimum seconds required to fill form (bots are too fast).
     */
    protected int $minimumSeconds = 3;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check POST, PUT, PATCH requests
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        // Check honeypot field - should be empty
        if ($request->filled($this->honeypotField)) {
            \Log::warning('Bot detected via honeypot', [
                'ip' => $request->ip(),
                'url' => $request->url(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return 200 OK to not reveal detection
            return response()->json(['message' => 'Success'], 200);
        }

        // Check timing - must take at least minimum seconds
        if ($request->has($this->timestampField)) {
            $formTime = (int) $request->input($this->timestampField);
            $elapsed = time() - $formTime;

            if ($elapsed < $this->minimumSeconds) {
                \Log::warning('Bot detected via timing', [
                    'ip' => $request->ip(),
                    'url' => $request->url(),
                    'elapsed' => $elapsed,
                ]);

                return response()->json(['message' => 'Success'], 200);
            }
        }

        return $next($request);
    }
}
