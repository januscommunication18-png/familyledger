<?php

use App\Http\Controllers\Backoffice\AuthController;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\ClientsController;
use App\Http\Controllers\Backoffice\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backoffice Routes
|--------------------------------------------------------------------------
|
| These routes are for the backoffice/admin panel.
| All routes are prefixed with 'backoffice' and use the 'backoffice' guard.
|
*/

// Guest routes (not logged in)
Route::middleware('guest:backoffice')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('backoffice.login');
    Route::post('/login', [AuthController::class, 'login'])->name('backoffice.login.submit');
    Route::get('/verify-code', [AuthController::class, 'showVerifyCode'])->name('backoffice.verify-code');
    Route::post('/verify-code', [AuthController::class, 'verifyCode'])->name('backoffice.verify-code.submit');
    Route::post('/resend-code', [AuthController::class, 'resendCode'])->name('backoffice.resend-code');

    // Password Reset
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('backoffice.forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('backoffice.forgot-password.submit');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('backoffice.reset-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('backoffice.reset-password.submit');
});

// Authenticated routes
Route::middleware('auth:backoffice')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('backoffice.logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('backoffice.dashboard');

    // Clients
    Route::prefix('clients')->name('backoffice.clients.')->group(function () {
        Route::get('/', [ClientsController::class, 'index'])->name('index');
        Route::get('/{client}', [ClientsController::class, 'show'])->name('show');
        Route::post('/{client}/toggle-status', [ClientsController::class, 'toggleStatus'])->name('toggleStatus');
        Route::post('/{client}/request-view-code', [ClientsController::class, 'requestViewCode'])->name('requestViewCode');
        Route::post('/{client}/verify-view-code', [ClientsController::class, 'verifyViewCode'])->name('verifyViewCode');
        Route::get('/{client}/data', [ClientsController::class, 'showData'])->name('data');
        Route::post('/{client}/revoke-access', [ClientsController::class, 'revokeViewAccess'])->name('revokeAccess');
    });

    // Settings
    Route::prefix('settings')->name('backoffice.settings.')->group(function () {
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::get('/change-password', [SettingsController::class, 'changePassword'])->name('changePassword');
        Route::put('/change-password', [SettingsController::class, 'updatePassword'])->name('changePassword.update');
    });
});
