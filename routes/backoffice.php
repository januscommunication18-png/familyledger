<?php

use App\Http\Controllers\Backoffice\AccountRecoveryController;
use App\Http\Controllers\Backoffice\AuthController;
use App\Http\Controllers\Backoffice\DashboardController;
use App\Http\Controllers\Backoffice\ClientsController;
use App\Http\Controllers\Backoffice\SettingsController;
use App\Http\Controllers\Backoffice\PackagePlanController;
use App\Http\Controllers\Backoffice\DiscountCodeController;
use App\Http\Controllers\Backoffice\DripCampaignController;
use App\Http\Controllers\Backoffice\InvoiceController;
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
    // Step 1: Request Access (enter email)
    Route::get('/', [AuthController::class, 'showRequestAccess'])->name('backoffice.request-access');
    Route::post('/request-access', [AuthController::class, 'requestAccess'])->name('backoffice.request-access.submit');

    // Step 2: Verify Access Code
    Route::get('/verify-access', [AuthController::class, 'showVerifyAccess'])->name('backoffice.verify-access');
    Route::post('/verify-access', [AuthController::class, 'verifyAccess'])->name('backoffice.verify-access.submit');
    Route::post('/resend-access-code', [AuthController::class, 'resendAccessCode'])->name('backoffice.resend-access-code');

    // Step 3: Login (enter password)
    Route::get('/login', [AuthController::class, 'showLogin'])->name('backoffice.login');
    Route::post('/login', [AuthController::class, 'login'])->name('backoffice.login.submit');

    // Step 4: Verify Security Code
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('backoffice.dashboard');

    // Clients
    Route::prefix('clients')->name('backoffice.clients.')->group(function () {
        Route::get('/', [ClientsController::class, 'index'])->name('index');
        Route::get('/{client}', [ClientsController::class, 'show'])->name('show');
        Route::post('/{client}/toggle-status', [ClientsController::class, 'toggleStatus'])->name('toggleStatus');
        Route::post('/{client}/request-view-code', [ClientsController::class, 'requestViewCode'])->name('requestViewCode');
        Route::post('/{client}/verify-view-code', [ClientsController::class, 'verifyViewCode'])->name('verifyViewCode');
        Route::get('/{client}/data', [ClientsController::class, 'showData'])->name('data');
        Route::post('/{client}/revoke-access', [ClientsController::class, 'revokeViewAccess'])->name('revokeAccess');
        Route::delete('/{client}/data', [ClientsController::class, 'destroyData'])->name('destroyData');
        Route::delete('/{client}', [ClientsController::class, 'destroy'])->name('destroy');
    });

    // Account Recovery
    Route::prefix('account-recovery')->name('backoffice.account-recovery.')->group(function () {
        Route::get('/', [AccountRecoveryController::class, 'index'])->name('index');
        Route::get('/search', [AccountRecoveryController::class, 'search'])->name('search');
        Route::get('/{client}', [AccountRecoveryController::class, 'show'])->name('show');
        Route::post('/{client}/verify-code', [AccountRecoveryController::class, 'verifyCode'])->name('verifyCode');
        Route::post('/{client}/change-email', [AccountRecoveryController::class, 'changeEmail'])->name('changeEmail');
        Route::post('/{client}/reset-password', [AccountRecoveryController::class, 'resetPassword'])->name('resetPassword');
        Route::post('/{client}/disable-2fa', [AccountRecoveryController::class, 'disable2fa'])->name('disable2fa');
        Route::post('/{client}/reset-phone', [AccountRecoveryController::class, 'resetPhone'])->name('resetPhone');
        Route::delete('/{client}/revoke-access', [AccountRecoveryController::class, 'revokeAccess'])->name('revokeAccess');
    });

    // Package Plans
    Route::prefix('package-plans')->name('backoffice.package-plans.')->group(function () {
        Route::get('/', [PackagePlanController::class, 'index'])->name('index');
        Route::get('/create', [PackagePlanController::class, 'create'])->name('create');
        Route::post('/', [PackagePlanController::class, 'store'])->name('store');
        Route::get('/{packagePlan}', [PackagePlanController::class, 'show'])->name('show');
        Route::get('/{packagePlan}/edit', [PackagePlanController::class, 'edit'])->name('edit');
        Route::put('/{packagePlan}', [PackagePlanController::class, 'update'])->name('update');
        Route::delete('/{packagePlan}', [PackagePlanController::class, 'destroy'])->name('destroy');
        Route::post('/{packagePlan}/toggle-status', [PackagePlanController::class, 'toggleStatus'])->name('toggleStatus');
    });

    // Discount Codes
    Route::prefix('discount-codes')->name('backoffice.discount-codes.')->group(function () {
        Route::get('/', [DiscountCodeController::class, 'index'])->name('index');
        Route::get('/create', [DiscountCodeController::class, 'create'])->name('create');
        Route::get('/generate-code', [DiscountCodeController::class, 'generateCode'])->name('generateCode');
        Route::post('/', [DiscountCodeController::class, 'store'])->name('store');
        Route::get('/{discountCode}', [DiscountCodeController::class, 'show'])->name('show');
        Route::get('/{discountCode}/edit', [DiscountCodeController::class, 'edit'])->name('edit');
        Route::put('/{discountCode}', [DiscountCodeController::class, 'update'])->name('update');
        Route::delete('/{discountCode}', [DiscountCodeController::class, 'destroy'])->name('destroy');
        Route::post('/{discountCode}/toggle-status', [DiscountCodeController::class, 'toggleStatus'])->name('toggleStatus');
    });

    // Drip Campaigns
    Route::prefix('drip-campaigns')->name('backoffice.drip-campaigns.')->group(function () {
        Route::get('/', [DripCampaignController::class, 'index'])->name('index');
        Route::get('/create', [DripCampaignController::class, 'create'])->name('create');
        Route::post('/', [DripCampaignController::class, 'store'])->name('store');
        Route::get('/{campaign}', [DripCampaignController::class, 'show'])->name('show');
        Route::get('/{campaign}/edit', [DripCampaignController::class, 'edit'])->name('edit');
        Route::put('/{campaign}', [DripCampaignController::class, 'update'])->name('update');
        Route::delete('/{campaign}', [DripCampaignController::class, 'destroy'])->name('destroy');
        Route::post('/{campaign}/toggle-status', [DripCampaignController::class, 'toggleStatus'])->name('toggleStatus');
        Route::get('/{campaign}/logs', [DripCampaignController::class, 'logs'])->name('logs');
        Route::post('/{campaign}/send-test', [DripCampaignController::class, 'sendTest'])->name('sendTest');

        // Email Steps
        Route::post('/{campaign}/steps', [DripCampaignController::class, 'addStep'])->name('steps.store');
        Route::put('/{campaign}/steps/{step}', [DripCampaignController::class, 'updateStep'])->name('steps.update');
        Route::delete('/{campaign}/steps/{step}', [DripCampaignController::class, 'deleteStep'])->name('steps.destroy');
        Route::post('/{campaign}/steps/reorder', [DripCampaignController::class, 'reorderSteps'])->name('steps.reorder');
    });

    // Invoices
    Route::prefix('invoices')->name('backoffice.invoices.')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/export', [InvoiceController::class, 'export'])->name('export');

        // TESTING ONLY - Remove after testing
        Route::get('/create-test', [InvoiceController::class, 'createTest'])->name('create-test');
        Route::post('/store-test', [InvoiceController::class, 'storeTest'])->name('store-test');
        // END TESTING ONLY

        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::post('/{invoice}/resend', [InvoiceController::class, 'resend'])->name('resend');
        Route::post('/{invoice}/resend-to-email', [InvoiceController::class, 'resendToEmail'])->name('resend-to-email');
        Route::post('/{invoice}/add-note', [InvoiceController::class, 'addNote'])->name('add-note');

        // TESTING ONLY - Remove after testing
        Route::delete('/{invoice}/delete-test', [InvoiceController::class, 'destroyTest'])->name('destroy-test');
        // END TESTING ONLY
    });

    // Settings
    Route::prefix('settings')->name('backoffice.settings.')->group(function () {
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::get('/change-password', [SettingsController::class, 'changePassword'])->name('changePassword');
        Route::put('/change-password', [SettingsController::class, 'updatePassword'])->name('changePassword.update');
        Route::get('/db-reset', [SettingsController::class, 'dbReset'])->name('dbReset');
        Route::delete('/db-reset', [SettingsController::class, 'performDbReset'])->name('dbReset.perform');
    });
});
