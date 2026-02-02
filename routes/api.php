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
use App\Http\Controllers\Api\V1\LegalDocumentApiController;
use App\Http\Controllers\Api\V1\SyncController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Auth\MfaController;

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
        // Registration
        Route::post('/register', [AuthController::class, 'register']);

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
        Route::post('/family-circles', [FamilyCircleController::class, 'store']);
        Route::get('/family-circles/{familyCircle}', [FamilyCircleController::class, 'show']);

        // Family Members (nested under family circles)
        Route::prefix('family-circles/{familyCircle}')->group(function () {
            Route::get('/members', [FamilyMemberController::class, 'index']);
            Route::post('/members', [FamilyMemberController::class, 'store']);
            Route::get('/members/{member}', [FamilyMemberController::class, 'show']);
            Route::put('/members/{member}', [FamilyMemberController::class, 'update']);
            Route::delete('/members/{member}', [FamilyMemberController::class, 'destroy']);
            Route::get('/resources', [FamilyCircleController::class, 'resources']);
            Route::get('/legal-documents', [FamilyCircleController::class, 'legalDocuments']);
        });

        // Family Member Lookup Data
        Route::get('/family-members', [FamilyMemberController::class, 'allMembers']);
        Route::get('/family-members/relationships', [FamilyMemberController::class, 'relationships']);
        Route::get('/family-members/immigration-statuses', [FamilyMemberController::class, 'immigrationStatuses']);
        Route::get('/family-members/blood-types', [FamilyMemberController::class, 'bloodTypes']);

        // Member Documents (nested under family-circles)
        Route::prefix('family-circles/{familyCircle}/members/{member}')->group(function () {
            // Documents
            Route::post('/documents', [FamilyMemberController::class, 'storeDocument']);
            Route::put('/documents/{document}', [FamilyMemberController::class, 'updateDocument']);
            Route::delete('/documents/{document}', [FamilyMemberController::class, 'deleteDocument']);

            // Medical Info
            Route::put('/medical-info', [FamilyMemberController::class, 'updateMedicalInfo']);

            // Allergies
            Route::post('/allergies', [FamilyMemberController::class, 'storeAllergy']);
            Route::put('/allergies/{allergy}', [FamilyMemberController::class, 'updateAllergy']);
            Route::delete('/allergies/{allergy}', [FamilyMemberController::class, 'deleteAllergy']);

            // Medications
            Route::post('/medications', [FamilyMemberController::class, 'storeMedication']);
            Route::put('/medications/{medication}', [FamilyMemberController::class, 'updateMedication']);
            Route::delete('/medications/{medication}', [FamilyMemberController::class, 'deleteMedication']);

            // Medical Conditions
            Route::post('/conditions', [FamilyMemberController::class, 'storeCondition']);
            Route::put('/conditions/{condition}', [FamilyMemberController::class, 'updateCondition']);
            Route::delete('/conditions/{condition}', [FamilyMemberController::class, 'deleteCondition']);

            // Healthcare Providers
            Route::post('/providers', [FamilyMemberController::class, 'storeProvider']);
            Route::put('/providers/{provider}', [FamilyMemberController::class, 'updateProvider']);
            Route::delete('/providers/{provider}', [FamilyMemberController::class, 'deleteProvider']);

            // Vaccinations
            Route::post('/vaccinations', [FamilyMemberController::class, 'storeVaccination']);
            Route::put('/vaccinations/{vaccination}', [FamilyMemberController::class, 'updateVaccination']);
            Route::delete('/vaccinations/{vaccination}', [FamilyMemberController::class, 'deleteVaccination']);

            // Emergency Contacts
            Route::post('/emergency-contacts', [FamilyMemberController::class, 'storeEmergencyContact']);
            Route::put('/emergency-contacts/{contact}', [FamilyMemberController::class, 'updateEmergencyContact']);
            Route::delete('/emergency-contacts/{contact}', [FamilyMemberController::class, 'deleteEmergencyContact']);

            // School Records
            Route::post('/school-records', [FamilyMemberController::class, 'storeSchoolRecord']);
            Route::put('/school-records/{schoolRecord}', [FamilyMemberController::class, 'updateSchoolRecord']);
            Route::delete('/school-records/{schoolRecord}', [FamilyMemberController::class, 'deleteSchoolRecord']);

            // Education Documents
            Route::post('/education-documents', [FamilyMemberController::class, 'storeEducationDocument']);
            Route::delete('/education-documents/{document}', [FamilyMemberController::class, 'deleteEducationDocument']);
        });

        // Assets
        Route::get('/assets', [AssetController::class, 'index']);
        Route::get('/assets/category/{category}', [AssetController::class, 'byCategory'])
            ->where('category', 'property|vehicle|valuable|inventory');
        Route::get('/assets/{asset}', [AssetController::class, 'show']);

        // Documents (Insurance, Tax Returns)
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::get('/documents/insurance', [DocumentController::class, 'insurancePolicies']);
        Route::post('/documents/insurance', [DocumentController::class, 'storeInsurancePolicy']);
        Route::get('/documents/insurance/{policy}', [DocumentController::class, 'showInsurancePolicy']);
        Route::get('/documents/tax-returns', [DocumentController::class, 'taxReturns']);
        Route::post('/documents/tax-returns', [DocumentController::class, 'storeTaxReturn']);
        Route::get('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'showTaxReturn']);

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::get('/expenses/categories', [ExpenseController::class, 'categories']);
        Route::get('/expenses/category/{category}', [ExpenseController::class, 'byCategory']);
        Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);

        // Budgets
        Route::get('/budgets', [BudgetController::class, 'index']);
        Route::post('/budgets', [BudgetController::class, 'store']);
        Route::get('/budgets/{budget}', [BudgetController::class, 'show']);

        // Goals & Tasks
        Route::get('/goals', [GoalController::class, 'index']);
        Route::post('/goals', [GoalController::class, 'store']);
        Route::get('/goals/{goal}', [GoalController::class, 'show']);
        Route::put('/goals/{goal}', [GoalController::class, 'update']);
        Route::delete('/goals/{goal}', [GoalController::class, 'destroy']);
        Route::post('/goals/{goal}/pause', [GoalController::class, 'pause']);
        Route::post('/goals/{goal}/resume', [GoalController::class, 'resume']);
        Route::post('/goals/{goal}/complete', [GoalController::class, 'complete']);
        Route::get('/tasks', [GoalController::class, 'tasks']);
        Route::post('/tasks', [GoalController::class, 'storeTask']);
        Route::get('/tasks/{task}', [GoalController::class, 'showTask']);
        Route::post('/tasks/{task}/toggle', [GoalController::class, 'toggleTask']);
        Route::delete('/tasks/{task}', [GoalController::class, 'destroyTask']);
        Route::post('/tasks/{task}/snooze', [GoalController::class, 'snoozeTask']);

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

        // Legal Documents
        Route::get('/legal-documents', [LegalDocumentApiController::class, 'index']);
        Route::get('/legal-documents/{legalDocument}', [LegalDocumentApiController::class, 'show']);

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

        // Sync (Offline Mode)
        Route::prefix('sync')->group(function () {
            Route::get('/pull', [SyncController::class, 'pull']);
            Route::post('/push', [SyncController::class, 'push']);
            Route::post('/resolve', [SyncController::class, 'resolve']);
            Route::get('/conflicts', [SyncController::class, 'conflicts']);
        });

        // Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingsController::class, 'getSettingsApi']);
            Route::post('/profile', [SettingsController::class, 'updateProfileApi']);
            Route::delete('/profile/avatar', [SettingsController::class, 'removeAvatarApi']);
            Route::post('/password', [SettingsController::class, 'updatePasswordApi']);
            Route::get('/sessions', [SettingsController::class, 'getSessionsApi']);
            Route::delete('/sessions/{session}', [SettingsController::class, 'revokeSessionApi']);
            Route::post('/sessions/revoke-all', [SettingsController::class, 'revokeAllSessionsApi']);
            Route::post('/notifications', [SettingsController::class, 'updateNotificationsApi']);
            Route::post('/appearance', [SettingsController::class, 'updateAppearanceApi']);
            Route::post('/privacy', [SettingsController::class, 'updatePrivacyApi']);
            Route::get('/export-data', [SettingsController::class, 'exportDataApi']);
            Route::post('/delete-account', [SettingsController::class, 'requestAccountDeletionApi']);
            Route::get('/login-activity', [SettingsController::class, 'getLoginActivityApi']);
            Route::post('/recovery-code/generate', [SettingsController::class, 'generateRecoveryCodeApi']);
            Route::post('/recovery-code', [SettingsController::class, 'saveRecoveryCodeApi']);

            // MFA Routes (existing methods return JSON)
            Route::post('/mfa/authenticator/setup', [MfaController::class, 'setupAuthenticator']);
            Route::post('/mfa/authenticator/confirm', [MfaController::class, 'confirmAuthenticator']);
            Route::post('/mfa/sms/enable', [MfaController::class, 'enableSmsMfa']);
            Route::post('/mfa/sms/confirm', [MfaController::class, 'confirmSmsMfa']);
            Route::post('/mfa/disable', [MfaController::class, 'disableMfa']);

            // Social Accounts
            Route::get('/social-accounts', [SettingsController::class, 'getSocialAccountsApi']);
            Route::delete('/social/{provider}', [SettingsController::class, 'disconnectSocialApi']);
        });
    });
});
