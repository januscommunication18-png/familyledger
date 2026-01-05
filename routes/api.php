<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\OtpController;
use App\Http\Controllers\Api\V1\Auth\SocialController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\OnboardingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FamilyCircleController;
use App\Http\Controllers\Api\V1\FamilyMemberController;
use App\Http\Controllers\Api\V1\AssetController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
|
| Mobile API routes for the Family Ledger React Native app.
| All routes are prefixed with /api/v1
|
*/

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Authentication Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        // Email OTP Authentication
        Route::post('/otp/request', [OtpController::class, 'request']);
        Route::post('/otp/verify', [OtpController::class, 'verify']);
        Route::post('/otp/resend', [OtpController::class, 'resend']);

        // Social Authentication (Mobile flow - receives tokens from native SDKs)
        Route::post('/social/{provider}', [SocialController::class, 'authenticate'])
            ->where('provider', 'google|apple');
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (require Sanctum authentication)
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        // Auth Management
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
        });

        // Onboarding
        Route::prefix('onboarding')->group(function () {
            Route::get('/status', [OnboardingController::class, 'status']);
            Route::post('/step/{step}', [OnboardingController::class, 'saveStep'])
                ->where('step', '[1-5]');
            Route::post('/complete', [OnboardingController::class, 'complete']);
        });

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

        // Family Circles
        Route::get('/family-circles', [FamilyCircleController::class, 'index']);
        Route::get('/family-circles/{familyCircle}', [FamilyCircleController::class, 'show']);

        // Family Members (nested under family circles)
        Route::prefix('family-circles/{familyCircle}')->group(function () {
            Route::get('/members', [FamilyMemberController::class, 'index']);
            Route::get('/members/{member}', [FamilyMemberController::class, 'show']);
        });

        // Assets
        Route::get('/assets', [AssetController::class, 'index']);
        Route::get('/assets/category/{category}', [AssetController::class, 'byCategory'])
            ->where('category', 'property|vehicle|valuable|inventory');
        Route::get('/assets/{asset}', [AssetController::class, 'show']);
    });
});
