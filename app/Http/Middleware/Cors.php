<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }

        // Add CORS headers
        $response->headers->set('Access-Control-Allow-Origin', $this->getAllowedOrigin($request));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }

    /**
     * Get the allowed origin based on the request.
     */
    protected function getAllowedOrigin(Request $request): string
    {
        $origin = $request->header('Origin');

        // Allowed origins for development and production
        $allowedOrigins = [
            'http://localhost:8081',      // Expo web dev
            'http://127.0.0.1:8081',      // Expo web dev
            'http://localhost:8082',      // Expo web dev alt port
            'http://127.0.0.1:8082',      // Expo web dev alt port
            'http://localhost:19006',     // Expo web dev alt port
            'http://localhost:3000',      // Local dev
            'http://127.0.0.1:8000',      // Laravel dev
            'http://localhost:8000',      // Laravel dev
            'https://meetfamilyhub.com',  // Production
            'https://staging.meetfamilyhub.com', // Staging
        ];

        // In development, allow all localhost origins
        if (app()->environment('local', 'development')) {
            if ($origin && (
                str_starts_with($origin, 'http://localhost') ||
                str_starts_with($origin, 'http://127.0.0.1')
            )) {
                return $origin;
            }
        }

        // Check if origin is in allowed list
        if ($origin && in_array($origin, $allowedOrigins)) {
            return $origin;
        }

        // Default to first allowed origin
        return $allowedOrigins[0];
    }
}