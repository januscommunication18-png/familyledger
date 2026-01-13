<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\OtpController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\SocialController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\OnboardingController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FamilyCircleController;
use App\Http\Controllers\Api\V1\FamilyMemberController;
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\ExpenseController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\JournalController;
use App\Http\Controllers\Api\V1\PetController;
use App\Http\Controllers\Api\V1\ShoppingController;
use App\Http\Controllers\Api\V1\ReminderController;
use App\Http\Controllers\Api\V1\PeopleController;
use App\Http\Controllers\Api\V1\ResourceController;
use App\Http\Controllers\Api\V1\BudgetController;
use App\Http\Controllers\Api\V1\CoparentingController;

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
        // Password Login
        Route::post('/login', [AuthController::class, 'login']);

        // Email OTP Authentication
        Route::post('/otp/request', [OtpController::class, 'request']);
        Route::post('/otp/verify', [OtpController::class, 'verify']);
        Route::post('/otp/resend', [OtpController::class, 'resend']);

        // Social Authentication (Mobile flow - receives tokens from native SDKs)
        Route::post('/social/{provider}', [SocialController::class, 'authenticate'])
            ->where('provider', 'google|apple');

        // Password Reset
        Route::post('/password/forgot', [PasswordResetController::class, 'sendResetCode']);
        Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
        Route::post('/password/resend', [PasswordResetController::class, 'resendCode']);
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

        // Documents (Insurance, Tax Returns)
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::get('/documents/insurance', [DocumentController::class, 'insurancePolicies']);
        Route::get('/documents/insurance/{policy}', [DocumentController::class, 'showInsurancePolicy']);
        Route::get('/documents/tax-returns', [DocumentController::class, 'taxReturns']);
        Route::get('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'showTaxReturn']);

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::get('/expenses/categories', [ExpenseController::class, 'categories']);
        Route::get('/expenses/category/{category}', [ExpenseController::class, 'byCategory']);
        Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);

        // Budgets
        Route::get('/budgets', [BudgetController::class, 'index']);
        Route::get('/budgets/{budget}', [BudgetController::class, 'show']);

        // Goals & Tasks
        Route::get('/goals', [GoalController::class, 'index']);
        Route::get('/goals/{goal}', [GoalController::class, 'show']);
        Route::get('/tasks', [GoalController::class, 'tasks']);
        Route::get('/tasks/{task}', [GoalController::class, 'showTask']);

        // Journal
        Route::get('/journal', [JournalController::class, 'index']);
        Route::get('/journal/type/{type}', [JournalController::class, 'byType']);
        Route::get('/journal/tags', [JournalController::class, 'tags']);
        Route::get('/journal/{entry}', [JournalController::class, 'show']);

        // Pets
        Route::get('/pets', [PetController::class, 'index']);
        Route::get('/pets/{pet}', [PetController::class, 'show']);
        Route::get('/pets/{pet}/vaccinations', [PetController::class, 'vaccinations']);
        Route::get('/pets/{pet}/medications', [PetController::class, 'medications']);

        // Shopping
        Route::get('/shopping', [ShoppingController::class, 'index']);
        Route::post('/shopping', [ShoppingController::class, 'store']);
        Route::get('/shopping/{list}', [ShoppingController::class, 'show']);
        Route::put('/shopping/{list}', [ShoppingController::class, 'update']);
        Route::delete('/shopping/{list}', [ShoppingController::class, 'destroy']);
        Route::get('/shopping/{list}/items', [ShoppingController::class, 'items']);
        Route::post('/shopping/{list}/items', [ShoppingController::class, 'addItem']);
        Route::put('/shopping/{list}/items/{item}', [ShoppingController::class, 'updateItem']);
        Route::delete('/shopping/{list}/items/{item}', [ShoppingController::class, 'deleteItem']);
        Route::post('/shopping/{list}/items/{item}/toggle', [ShoppingController::class, 'toggleItem']);
        Route::post('/shopping/{list}/clear-checked', [ShoppingController::class, 'clearChecked']);

        // Reminders
        Route::get('/reminders', [ReminderController::class, 'index']);
        Route::get('/reminders/overdue', [ReminderController::class, 'overdue']);
        Route::get('/reminders/upcoming', [ReminderController::class, 'upcoming']);
        Route::get('/reminders/{reminder}', [ReminderController::class, 'show']);

        // People (Contacts)
        Route::get('/people', [PeopleController::class, 'index']);
        Route::get('/people/search', [PeopleController::class, 'search']);
        Route::get('/people/relationship/{relationship}', [PeopleController::class, 'byRelationship']);
        Route::get('/people/{person}', [PeopleController::class, 'show']);

        // Family Resources
        Route::get('/resources', [ResourceController::class, 'index']);
        Route::get('/resources/type/{type}', [ResourceController::class, 'byType']);
        Route::get('/resources/{resource}', [ResourceController::class, 'show']);

        // Co-parenting
        Route::prefix('coparenting')->group(function () {
            Route::get('/', [CoparentingController::class, 'index']);
            Route::get('/children', [CoparentingController::class, 'children']);
            Route::get('/children/{child}', [CoparentingController::class, 'showChild']);
            Route::get('/schedule', [CoparentingController::class, 'schedule']);
            Route::get('/activities', [CoparentingController::class, 'activities']);
            Route::get('/actual-time', [CoparentingController::class, 'actualTime']);
            Route::get('/conversations', [CoparentingController::class, 'conversations']);
            Route::post('/conversations', [CoparentingController::class, 'createConversation']);
            Route::get('/conversations/{conversation}', [CoparentingController::class, 'showConversation']);
            Route::post('/conversations/{conversation}/messages', [CoparentingController::class, 'sendMessage']);
        });
    });
});
