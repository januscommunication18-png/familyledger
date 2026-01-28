<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('backoffice')
                ->group(base_path('routes/backoffice.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global security headers for HIPAA compliance
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Force HTTPS in production
        $middleware->append(\App\Http\Middleware\ForceHttps::class);

        // CORS for API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\Cors::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'honeypot' => \App\Http\Middleware\HoneypotProtection::class,
            'security.code' => \App\Http\Middleware\SecurityCodeGate::class,
            'onboarding' => \App\Http\Middleware\EnsureOnboardingComplete::class,
            'plan.limit' => \App\Http\Middleware\CheckPlanLimits::class,
        ]);

        // Exclude webhook routes from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report to LaraBug but prevent cascading errors
        $exceptions->reportable(function (\Throwable $e) {
            // Skip reporting errors that would cause cascading issues
            $message = $e->getMessage();
            if (str_contains($message, 'memory size') ||
                str_contains($message, 'Data too long') ||
                str_contains($message, 'larabug')) {
                return false; // Don't report these to prevent cascading
            }

            if (app()->bound('larabug')) {
                try {
                    app('larabug')->handle($e);
                } catch (\Throwable $larabugError) {
                    // Silently fail if LaraBug itself errors
                    \Log::warning('LaraBug reporting failed: ' . $larabugError->getMessage());
                }
            }
        });

        // Render friendly error pages in production
        $exceptions->render(function (\Throwable $e, Request $request) {
            // Don't modify responses in local/development
            if (app()->environment('local', 'development')) {
                return null;
            }

            // API requests get JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong. Please try again later.',
                ], $status);
            }

            // 404 errors
            if ($e instanceof NotFoundHttpException) {
                return response()->view('errors.404', [], 404);
            }

            // All other errors in production - show friendly page
            return response()->view('errors.500', [], 500);
        });
    })->create();
