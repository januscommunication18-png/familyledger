<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global security headers for HIPAA compliance
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // Force HTTPS in production
        $middleware->append(\App\Http\Middleware\ForceHttps::class);

        // Middleware aliases
        $middleware->alias([
            'honeypot' => \App\Http\Middleware\HoneypotProtection::class,
            'security.code' => \App\Http\Middleware\SecurityCodeGate::class,
            'onboarding' => \App\Http\Middleware\EnsureOnboardingComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
