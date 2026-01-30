<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\OtpAuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\FamilyCircleController;
use App\Http\Controllers\FamilyMemberController;
use App\Http\Controllers\MemberDocumentController;
use App\Http\Controllers\MemberEmergencyContactController;
use App\Http\Controllers\MemberMedicalController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\SecurityCodeController;
use App\Http\Controllers\ImageVerificationController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\FamilyResourceController;
use App\Http\Controllers\ShoppingListController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\CoparentingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RemindersController;
use App\Http\Controllers\CoparentMessagesController;
use App\Http\Controllers\PendingCoparentEditController;
use App\Http\Controllers\CoparentAssetController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Security Code Routes (No middleware - must be accessible)
|--------------------------------------------------------------------------
*/

Route::get('/security-code', [SecurityCodeController::class, 'show'])->name('security.code');
Route::post('/security-code', [SecurityCodeController::class, 'verify'])->name('security.verify');

/*
|--------------------------------------------------------------------------
| Public Routes (Protected by Security Code)
|--------------------------------------------------------------------------
*/

Route::middleware('security.code')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    });

    // Collaborator Invite Accept (Public - invitees may not have an account yet)
    Route::get('/invite/{token}', [CollaboratorController::class, 'acceptForm'])->name('collaborator.accept');
    Route::post('/invite/{token}/accept', [CollaboratorController::class, 'acceptInvite'])->name('collaborator.accept.process');
    Route::post('/invite/{token}/decline', [CollaboratorController::class, 'declineInvite'])->name('collaborator.decline');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes - Guest Only (Protected by Security Code)
|--------------------------------------------------------------------------
*/

Route::middleware(['security.code', 'guest'])->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registration
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Email OTP (Passwordless) Login
    Route::post('/auth/otp/request', [OtpAuthController::class, 'requestOtp']);
    Route::post('/auth/otp/verify', [OtpAuthController::class, 'verifyOtp']);
    Route::post('/auth/otp/resend', [OtpAuthController::class, 'resendOtp']);

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode'])->name('password.email');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
    Route::post('/forgot-password/resend', [PasswordResetController::class, 'resendCode'])->name('password.resend');

    // Social OAuth Routes
    Route::get('/auth/{provider}', [SocialAuthController::class, 'redirect'])
        ->where('provider', 'google|apple|facebook');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->where('provider', 'google|apple|facebook');
});

/*
|--------------------------------------------------------------------------
| MFA Routes - Pending Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('web')->group(function () {
    Route::get('/auth/mfa', [MfaController::class, 'show'])->name('mfa.show');
    Route::post('/auth/mfa/verify', [MfaController::class, 'verify']);
    Route::post('/auth/mfa/email/send', [MfaController::class, 'sendEmailCode']);
    Route::post('/auth/mfa/sms/send', [MfaController::class, 'sendSmsCode']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Protected by Security Code)
|--------------------------------------------------------------------------
*/

Route::middleware(['security.code', 'auth'])->group(function () {
    // Broadcast authentication routes for real-time features
    Broadcast::routes();

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email Verification
    Route::get('/verify-email', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
    Route::post('/verify-email', [RegisterController::class, 'verifyEmail']);
    Route::post('/verify-email/resend', [RegisterController::class, 'resendVerification']);

    // MFA Settings
    Route::post('/settings/mfa/sms/enable', [MfaController::class, 'enableSmsMfa']);
    Route::post('/settings/mfa/sms/confirm', [MfaController::class, 'confirmSmsMfa']);
    Route::post('/settings/mfa/authenticator/setup', [MfaController::class, 'setupAuthenticator']);
    Route::post('/settings/mfa/authenticator/confirm', [MfaController::class, 'confirmAuthenticator']);
    Route::post('/settings/mfa/disable', [MfaController::class, 'disableMfa']);

    // Unlink Social Account
    Route::delete('/settings/social/{provider}', [SocialAuthController::class, 'unlink'])
        ->where('provider', 'google|apple|facebook');

    // Dashboard (requires email verification AND onboarding completion)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware(['verified', 'onboarding'])
        ->name('dashboard');

    // Global Search
    Route::get('/search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])
        ->middleware(['verified', 'onboarding'])
        ->name('search');

    // Family Circle
    Route::middleware(['verified', 'onboarding'])->prefix('family-circle')->name('family-circle.')->group(function () {
        Route::get('/', [FamilyCircleController::class, 'index'])->name('index');
        Route::post('/', [FamilyCircleController::class, 'store'])->middleware('plan.limit:family_circles')->name('store');
        Route::get('/{familyCircle}', [FamilyCircleController::class, 'show'])->name('show');
        Route::get('/{familyCircle}/owner', [FamilyCircleController::class, 'showOwner'])->name('owner.show');
        Route::put('/{familyCircle}', [FamilyCircleController::class, 'update'])->name('update');
        Route::delete('/{familyCircle}', [FamilyCircleController::class, 'destroy'])->name('destroy');

        // Family Members
        Route::get('/{familyCircle}/members/create', [FamilyMemberController::class, 'create'])->name('member.create');
        Route::post('/{familyCircle}/members', [FamilyMemberController::class, 'store'])->middleware('plan.limit:family_members')->name('member.store');
        Route::get('/{familyCircle}/members/{member}', [FamilyMemberController::class, 'show'])->name('member.show');
        Route::get('/{familyCircle}/members/{member}/edit', [FamilyMemberController::class, 'edit'])->name('member.edit');
        Route::put('/{familyCircle}/members/{member}', [FamilyMemberController::class, 'update'])->name('member.update');
        Route::post('/{familyCircle}/members/{member}/field', [FamilyMemberController::class, 'updateField'])->name('member.update-field');
        Route::delete('/{familyCircle}/members/{member}', [FamilyMemberController::class, 'destroy'])->name('member.destroy');

        // Member Document Pages
        Route::get('/{familyCircle}/members/{member}/drivers-license', [MemberDocumentController::class, 'driversLicense'])->name('member.drivers-license');
        Route::get('/{familyCircle}/members/{member}/passport', [MemberDocumentController::class, 'passport'])->name('member.passport');
        Route::get('/{familyCircle}/members/{member}/social-security', [MemberDocumentController::class, 'socialSecurity'])->name('member.social-security');
        Route::get('/{familyCircle}/members/{member}/birth-certificate', [MemberDocumentController::class, 'birthCertificate'])->name('member.birth-certificate');

        // Medical Info Page
        Route::get('/{familyCircle}/members/{member}/medical-info', [MemberMedicalController::class, 'show'])->name('member.medical-info');

        // Emergency Contacts Page
        Route::get('/{familyCircle}/members/{member}/emergency-contacts', [MemberEmergencyContactController::class, 'show'])->name('member.emergency-contacts');

        // Education Info Page
        Route::get('/{familyCircle}/members/{member}/education', [FamilyMemberController::class, 'educationInfo'])->name('member.education-info');

        // School Records (multiple)
        Route::get('/{familyCircle}/members/{member}/education/school/create', [FamilyMemberController::class, 'createSchoolRecord'])->name('member.education.school.create');
        Route::post('/{familyCircle}/members/{member}/education/school', [FamilyMemberController::class, 'storeSchoolRecord'])->name('member.education.school.store');
        Route::get('/{familyCircle}/members/{member}/education/school/{schoolRecord}', [FamilyMemberController::class, 'showSchoolRecord'])->name('member.education.school.show');
        Route::get('/{familyCircle}/members/{member}/education/school/{schoolRecord}/edit', [FamilyMemberController::class, 'editSchoolRecord'])->name('member.education.school.edit');
        Route::put('/{familyCircle}/members/{member}/education/school/{schoolRecord}', [FamilyMemberController::class, 'updateSchoolRecord'])->name('member.education.school.update');
        Route::delete('/{familyCircle}/members/{member}/education/school/{schoolRecord}', [FamilyMemberController::class, 'destroySchoolRecord'])->name('member.education.school.destroy');

        // Education Documents
        Route::post('/{familyCircle}/members/{member}/education/documents', [FamilyMemberController::class, 'storeEducationDocument'])->middleware('plan.limit:documents')->name('member.education.document.store');
        Route::get('/{familyCircle}/members/{member}/education/documents/{document}/download', [FamilyMemberController::class, 'downloadEducationDocument'])->name('member.education.document.download');
        Route::delete('/{familyCircle}/members/{member}/education/documents/{document}', [FamilyMemberController::class, 'destroyEducationDocument'])->name('member.education.document.destroy');
    });

    // Member Documents (accessible directly via member ID)
    Route::middleware(['verified', 'onboarding'])->prefix('member')->name('member.')->group(function () {
        // Medical Info
        Route::post('/{member}/medical', [FamilyMemberController::class, 'storeMedicalInfo'])->name('medical.store');
        Route::post('/{member}/medical/field', [FamilyMemberController::class, 'updateMedicalField'])->name('medical.update-field');
        Route::put('/{member}/medical-info', [MemberMedicalController::class, 'updateMedicalInfo'])->name('medical-info.update');

        // Allergies
        Route::post('/{member}/allergies', [MemberMedicalController::class, 'storeAllergy'])->name('allergy.store');
        Route::put('/{member}/allergies/{allergy}', [MemberMedicalController::class, 'updateAllergy'])->name('allergy.update');
        Route::delete('/{member}/allergies/{allergy}', [MemberMedicalController::class, 'destroyAllergy'])->name('allergy.destroy');

        // Healthcare Providers
        Route::post('/{member}/providers', [MemberMedicalController::class, 'storeProvider'])->name('provider.store');
        Route::put('/{member}/providers/{provider}', [MemberMedicalController::class, 'updateProvider'])->name('provider.update');
        Route::delete('/{member}/providers/{provider}', [MemberMedicalController::class, 'destroyProvider'])->name('provider.destroy');

        // Medications
        Route::post('/{member}/medications', [MemberMedicalController::class, 'storeMedication'])->name('medication.store');
        Route::put('/{member}/medications/{medication}', [MemberMedicalController::class, 'updateMedication'])->name('medication.update');
        Route::delete('/{member}/medications/{medication}', [MemberMedicalController::class, 'destroyMedication'])->name('medication.destroy');

        // Medical Conditions
        Route::post('/{member}/conditions', [MemberMedicalController::class, 'storeCondition'])->name('condition.store');
        Route::put('/{member}/conditions/{condition}', [MemberMedicalController::class, 'updateCondition'])->name('condition.update');
        Route::delete('/{member}/conditions/{condition}', [MemberMedicalController::class, 'destroyCondition'])->name('condition.destroy');

        // Vaccinations
        Route::post('/{member}/vaccinations', [MemberMedicalController::class, 'storeVaccination'])->name('vaccination.store');
        Route::put('/{member}/vaccinations/{vaccination}', [MemberMedicalController::class, 'updateVaccination'])->name('vaccination.update');
        Route::delete('/{member}/vaccinations/{vaccination}', [MemberMedicalController::class, 'destroyVaccination'])->name('vaccination.destroy');
        Route::get('/{member}/vaccinations/{vaccination}/download', [MemberMedicalController::class, 'downloadVaccinationDocument'])->name('vaccination.download');

        // School Info
        Route::post('/{member}/school', [FamilyMemberController::class, 'storeSchoolInfo'])->name('school.store');

        // Contacts
        Route::post('/{member}/contacts', [FamilyMemberController::class, 'storeContact'])->name('contact.store');
        Route::delete('/{member}/contacts/{contact}', [FamilyMemberController::class, 'destroyContact'])->name('contact.destroy');

        // Emergency Contacts
        Route::post('/{member}/emergency-contacts', [MemberEmergencyContactController::class, 'store'])->name('emergency-contact.store');
        Route::put('/{member}/emergency-contacts/{contact}', [MemberEmergencyContactController::class, 'update'])->name('emergency-contact.update');
        Route::delete('/{member}/emergency-contacts/{contact}', [MemberEmergencyContactController::class, 'destroy'])->name('emergency-contact.destroy');

        // Documents
        Route::get('/{member}/documents', [MemberDocumentController::class, 'index'])->name('documents.index');
        Route::post('/{member}/documents', [MemberDocumentController::class, 'store'])->middleware('plan.limit:documents')->name('documents.store');
        Route::get('/{member}/documents/{document}', [MemberDocumentController::class, 'show'])->name('documents.show');
        Route::put('/{member}/documents/{document}', [MemberDocumentController::class, 'update'])->name('documents.update');
        Route::delete('/{member}/documents/{document}', [MemberDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::get('/{member}/documents/{document}/image/{type}', [MemberDocumentController::class, 'image'])
            ->where('type', 'front|back')
            ->name('documents.image');
    });

    // Assets
    Route::middleware(['verified', 'onboarding'])->prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}', [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');

        // Document management
        Route::post('/{asset}/documents', [AssetController::class, 'uploadDocument'])->middleware('plan.limit:documents')->name('documents.upload');
        Route::delete('/{asset}/documents/{document}', [AssetController::class, 'deleteDocument'])->name('documents.destroy');
        Route::get('/{asset}/documents/{document}/download', [AssetController::class, 'downloadDocument'])->name('documents.download');
        Route::get('/{asset}/documents/{document}/view', [AssetController::class, 'viewDocument'])->name('documents.view');
    });

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->middleware(['verified', 'onboarding'])->name('documents.index');

    // Insurance Policies
    Route::get('/documents/insurance/create', [DocumentController::class, 'createInsurance'])->middleware(['verified', 'onboarding'])->name('documents.insurance.create');
    Route::post('/documents/insurance', [DocumentController::class, 'storeInsurance'])->middleware(['verified', 'onboarding', 'plan.limit:documents'])->name('documents.insurance.store');
    Route::get('/documents/insurance/{insurance}', [DocumentController::class, 'showInsurance'])->middleware(['verified', 'onboarding'])->name('documents.insurance.show');
    Route::get('/documents/insurance/{insurance}/edit', [DocumentController::class, 'editInsurance'])->middleware(['verified', 'onboarding'])->name('documents.insurance.edit');
    Route::put('/documents/insurance/{insurance}', [DocumentController::class, 'updateInsurance'])->middleware(['verified', 'onboarding'])->name('documents.insurance.update');
    Route::delete('/documents/insurance/{insurance}', [DocumentController::class, 'destroyInsurance'])->middleware(['verified', 'onboarding'])->name('documents.insurance.destroy');
    Route::get('/documents/insurance/{insurance}/card/{type}', [DocumentController::class, 'insuranceCardImage'])->middleware(['verified', 'onboarding'])->name('documents.insurance.card');

    // Tax Returns
    Route::get('/documents/tax-returns/create', [DocumentController::class, 'createTaxReturn'])->middleware(['verified', 'onboarding'])->name('documents.tax-returns.create');
    Route::post('/documents/tax-returns', [DocumentController::class, 'storeTaxReturn'])->middleware(['verified', 'onboarding', 'plan.limit:documents'])->name('documents.tax-returns.store');
    Route::get('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'showTaxReturn'])->middleware(['verified', 'onboarding'])->name('documents.tax-returns.show');
    Route::get('/documents/tax-returns/{taxReturn}/edit', [DocumentController::class, 'editTaxReturn'])->middleware(['verified', 'onboarding'])->name('documents.tax-returns.edit');
    Route::put('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'updateTaxReturn'])->middleware(['verified', 'onboarding'])->name('documents.tax-returns.update');
    Route::delete('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'destroyTaxReturn'])->middleware(['verified', 'onboarding'])->name('documents.tax-returns.destroy');
    Route::get('/documents/tax-returns/{taxReturn}/download/{type}/{index}', [DocumentController::class, 'downloadTaxReturnFile'])->middleware(['verified', 'onboarding'])->name('documents.tax-returns.download');

    // Legal Documents
    Route::middleware(['verified', 'onboarding'])->prefix('legal')->name('legal.')->group(function () {
        Route::get('/', [LegalDocumentController::class, 'index'])->name('index');
        Route::get('/create', [LegalDocumentController::class, 'create'])->name('create');
        Route::post('/', [LegalDocumentController::class, 'store'])->middleware('plan.limit:documents')->name('store');
        Route::get('/{legalDocument}', [LegalDocumentController::class, 'show'])->name('show');
        Route::get('/{legalDocument}/edit', [LegalDocumentController::class, 'edit'])->name('edit');
        Route::put('/{legalDocument}', [LegalDocumentController::class, 'update'])->name('update');
        Route::delete('/{legalDocument}', [LegalDocumentController::class, 'destroy'])->name('destroy');

        // File management
        Route::get('/{legalDocument}/files/{file}/download', [LegalDocumentController::class, 'downloadFile'])->name('files.download');
        Route::get('/{legalDocument}/files/{file}/view', [LegalDocumentController::class, 'viewFile'])->name('files.view');
        Route::delete('/{legalDocument}/files/{file}', [LegalDocumentController::class, 'destroyFile'])->name('files.destroy');
    });

    // Family Resources
    Route::middleware(['verified', 'onboarding'])->prefix('family-resources')->name('family-resources.')->group(function () {
        Route::get('/', [FamilyResourceController::class, 'index'])->name('index');
        Route::get('/create', [FamilyResourceController::class, 'create'])->name('create');
        Route::post('/', [FamilyResourceController::class, 'store'])->name('store');
        Route::get('/{familyResource}', [FamilyResourceController::class, 'show'])->name('show');
        Route::get('/{familyResource}/edit', [FamilyResourceController::class, 'edit'])->name('edit');
        Route::put('/{familyResource}', [FamilyResourceController::class, 'update'])->name('update');
        Route::delete('/{familyResource}', [FamilyResourceController::class, 'destroy'])->name('destroy');

        // File management
        Route::get('/{familyResource}/files/{file}/download', [FamilyResourceController::class, 'downloadFile'])->name('files.download');
        Route::get('/{familyResource}/files/{file}/view', [FamilyResourceController::class, 'viewFile'])->name('files.view');
        Route::delete('/{familyResource}/files/{file}', [FamilyResourceController::class, 'destroyFile'])->name('files.destroy');
    });

    // Goals & To-Do
    Route::middleware(['verified', 'onboarding'])->prefix('goals-todo')->name('goals-todo.')->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');

        // Goals
        Route::get('/goals', [GoalController::class, 'index'])->name('goals.index');
        Route::get('/goals/templates', [GoalController::class, 'templates'])->name('goals.templates');
        Route::get('/goals/create', [GoalController::class, 'create'])->name('goals.create');
        Route::post('/goals', [GoalController::class, 'store'])->name('goals.store');
        Route::post('/goals/from-template/{template}', [GoalController::class, 'createFromTemplate'])->name('goals.from-template');
        Route::get('/goals/{goal}', [GoalController::class, 'show'])->name('goals.show');
        Route::get('/goals/{goal}/edit', [GoalController::class, 'edit'])->name('goals.edit');
        Route::put('/goals/{goal}', [GoalController::class, 'update'])->name('goals.update');
        Route::delete('/goals/{goal}', [GoalController::class, 'destroy'])->name('goals.destroy');
        Route::post('/goals/{goal}/progress', [GoalController::class, 'updateProgress'])->name('goals.progress');
        Route::post('/goals/{goal}/status', [GoalController::class, 'toggleStatus'])->name('goals.status');
        Route::post('/goals/{goal}/check-in', [GoalController::class, 'checkIn'])->name('goals.check-in');
        Route::post('/goals/{goal}/mark-done', [GoalController::class, 'markDone'])->name('goals.mark-done');
        Route::post('/goals/{goal}/skip', [GoalController::class, 'skip'])->name('goals.skip');
        Route::post('/goals/{goal}/claim-reward', [GoalController::class, 'claimReward'])->name('goals.claim-reward');

        // Tasks
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
        Route::post('/tasks/{task}/toggle-series', [TaskController::class, 'toggleSeries'])->name('tasks.toggle-series');
        Route::get('/tasks/{task}/occurrences', [TaskController::class, 'getOccurrences'])->name('tasks.occurrences');
        Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');

        // Task Occurrences
        Route::post('/occurrences/{occurrence}/complete', [TaskController::class, 'completeOccurrence'])->name('occurrences.complete');
        Route::post('/occurrences/{occurrence}/reopen', [TaskController::class, 'reopenOccurrence'])->name('occurrences.reopen');
        Route::post('/occurrences/{occurrence}/skip', [TaskController::class, 'skipOccurrence'])->name('occurrences.skip');
        Route::post('/occurrences/{occurrence}/snooze', [TaskController::class, 'snoozeOccurrence'])->name('occurrences.snooze');
    });

    // Backward compatibility redirect from old lists route
    Route::get('/lists', function () {
        return redirect()->route('goals-todo.index');
    })->middleware(['verified', 'onboarding']);

    // Collaborators
    Route::middleware(['verified', 'onboarding'])->prefix('collaborators')->name('collaborators.')->group(function () {
        Route::get('/', [CollaboratorController::class, 'index'])->name('index');
        Route::get('/invite', [CollaboratorController::class, 'create'])->name('create');
        Route::post('/', [CollaboratorController::class, 'store'])->name('store');
        Route::get('/{collaborator}', [CollaboratorController::class, 'show'])->name('show');
        Route::get('/{collaborator}/edit', [CollaboratorController::class, 'edit'])->name('edit');
        Route::put('/{collaborator}', [CollaboratorController::class, 'update'])->name('update');
        Route::patch('/{collaborator}/deactivate', [CollaboratorController::class, 'deactivate'])->name('deactivate');
        Route::patch('/{collaborator}/activate', [CollaboratorController::class, 'activate'])->name('activate');
        Route::patch('/{collaborator}/role', [CollaboratorController::class, 'updateRole'])->name('updateRole');
        Route::post('/{collaborator}/resend-welcome', [CollaboratorController::class, 'resendWelcome'])->name('resendWelcome');
        Route::post('/{collaborator}/send-reminder', [CollaboratorController::class, 'sendReminder'])->name('sendReminder');
        Route::delete('/{collaborator}', [CollaboratorController::class, 'destroy'])->name('destroy');

        // Invite management
        Route::get('/invites/{invite}', [CollaboratorController::class, 'showInvite'])->name('invites.show');
        Route::post('/invites/{invite}/resend', [CollaboratorController::class, 'resendInvite'])->name('invites.resend');
        Route::delete('/invites/{invite}', [CollaboratorController::class, 'revokeInvite'])->name('invites.revoke');
    });

    // Co-Parenting
    Route::middleware(['verified', 'onboarding'])->prefix('coparenting')->name('coparenting.')->group(function () {
        // Main pages
        Route::get('/', [CoparentingController::class, 'index'])->name('index');
        Route::get('/intro', [CoparentingController::class, 'intro'])->name('intro');

        // Mode toggle
        Route::post('/enter-mode', [CoparentingController::class, 'enterMode'])->name('enter-mode');
        Route::post('/exit-mode', [CoparentingController::class, 'exitMode'])->name('exit-mode');

        // Invite flow
        Route::get('/invite', [CoparentingController::class, 'inviteForm'])->name('invite');
        Route::post('/invite', [CoparentingController::class, 'sendInvite'])->name('invite.send');
        Route::post('/invite/{invite}/resend', [CoparentingController::class, 'resendInvite'])->name('invite.resend');

        // Children management
        Route::get('/children', [CoparentingController::class, 'children'])->name('children');
        Route::get('/children/{child}', [CoparentingController::class, 'showChild'])->name('children.show');
        Route::get('/children/{child}/access', [CoparentingController::class, 'manageAccess'])->name('children.access');
        Route::put('/children/{child}/access', [CoparentingController::class, 'updateAccess'])->name('children.access.update');

        // Calendar & Schedule Management
        Route::get('/calendar', [CoparentingController::class, 'calendar'])->name('calendar');
        Route::get('/calendar/events', [CoparentingController::class, 'calendarEvents'])->name('calendar.events');
        Route::post('/schedule', [CoparentingController::class, 'storeSchedule'])->name('schedule.store');
        Route::put('/schedule/{schedule}', [CoparentingController::class, 'updateSchedule'])->name('schedule.update');
        Route::delete('/schedule/{schedule}', [CoparentingController::class, 'deleteSchedule'])->name('schedule.delete');
        Route::post('/schedule/{schedule}/block', [CoparentingController::class, 'addScheduleBlock'])->name('schedule.block.store');

        // Activities
        Route::get('/activities', [CoparentingController::class, 'activities'])->name('activities');
        Route::get('/activities/events', [CoparentingController::class, 'activityEvents'])->name('activities.events');
        Route::get('/activities/{activity}', [CoparentingController::class, 'showActivity'])->name('activities.show');
        Route::post('/activities', [CoparentingController::class, 'storeActivity'])->name('activities.store');
        Route::put('/activities/{activity}', [CoparentingController::class, 'updateActivity'])->name('activities.update');
        Route::delete('/activities/{activity}', [CoparentingController::class, 'deleteActivity'])->name('activities.delete');

        // Actual Time Tracking
        Route::get('/actual-time', [CoparentingController::class, 'actualTime'])->name('actual-time');
        Route::get('/actual-time/stats', [CoparentingController::class, 'actualTimeStats'])->name('actual-time.stats');
        Route::post('/actual-time', [CoparentingController::class, 'storeActualTime'])->name('actual-time.store');
        Route::put('/actual-time/{checkin}', [CoparentingController::class, 'updateActualTime'])->name('actual-time.update');
        Route::delete('/actual-time/{checkin}', [CoparentingController::class, 'deleteActualTime'])->name('actual-time.delete');

        // Placeholder pages
        Route::get('/child-info', [CoparentingController::class, 'childInfo'])->name('child-info');
        Route::get('/expenses', [CoparentingController::class, 'expenses'])->name('expenses');
        Route::get('/parenting-plan', [CoparentingController::class, 'parentingPlan'])->name('parenting-plan');

        // Coparent Assets
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [CoparentAssetController::class, 'index'])->name('index');
            Route::get('/create', [CoparentAssetController::class, 'create'])->name('create');
            Route::post('/', [CoparentAssetController::class, 'store'])->name('store');
            Route::get('/{asset}', [CoparentAssetController::class, 'show'])->name('show');
        });

        // Secure Messages
        Route::prefix('messages')->name('messages.')->group(function () {
            // Static routes first (before wildcard routes)
            Route::get('/', [CoparentMessagesController::class, 'index'])->name('index');
            Route::get('/create', [CoparentMessagesController::class, 'create'])->name('create');
            Route::post('/', [CoparentMessagesController::class, 'store'])->name('store');
            Route::get('/templates', [CoparentMessagesController::class, 'templates'])->name('templates');
            Route::get('/attachments/{attachment}/download', [CoparentMessagesController::class, 'downloadAttachment'])->name('downloadAttachment');
            Route::get('/message/{message}/edit', [CoparentMessagesController::class, 'edit'])->name('editMessage');
            Route::put('/message/{message}', [CoparentMessagesController::class, 'update'])->name('updateMessage');
            Route::get('/message/{message}/history', [CoparentMessagesController::class, 'showEditHistory'])->name('editHistory');
            Route::post('/message/{message}/reaction', [CoparentMessagesController::class, 'toggleReaction'])->name('toggleReaction');

            // Conversation routes (wildcard - must be last)
            Route::get('/{conversation}', [CoparentMessagesController::class, 'show'])->name('show');
            Route::post('/{conversation}/messages', [CoparentMessagesController::class, 'storeMessage'])->name('storeMessage');
            Route::get('/{conversation}/export-pdf', [CoparentMessagesController::class, 'exportPdf'])->name('exportPdf');
            Route::get('/{conversation}/export-csv', [CoparentMessagesController::class, 'exportCsv'])->name('exportCsv');
            Route::post('/{conversation}/attachments', [CoparentMessagesController::class, 'uploadAttachment'])->name('uploadAttachment');
        });

        // Pending Edits (Owner review of coparent edit requests)
        Route::prefix('pending-edits')->name('pending-edits.')->group(function () {
            Route::get('/', [PendingCoparentEditController::class, 'index'])->name('index');
            Route::get('/count', [PendingCoparentEditController::class, 'count'])->name('count');
            Route::get('/history', [PendingCoparentEditController::class, 'history'])->name('history');
            Route::get('/{pendingEdit}', [PendingCoparentEditController::class, 'show'])->name('show');
            Route::post('/{pendingEdit}/approve', [PendingCoparentEditController::class, 'approve'])->name('approve');
            Route::post('/{pendingEdit}/reject', [PendingCoparentEditController::class, 'reject'])->name('reject');
            Route::post('/bulk-approve', [PendingCoparentEditController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [PendingCoparentEditController::class, 'bulkReject'])->name('bulk-reject');
        });
    });

    // Reminders
    Route::middleware(['verified', 'onboarding'])->prefix('reminders')->name('reminders.')->group(function () {
        Route::get('/', [RemindersController::class, 'index'])->name('index');
        Route::post('/{reminder}/complete', [RemindersController::class, 'complete'])->name('complete');
        Route::post('/{reminder}/snooze', [RemindersController::class, 'snooze'])->name('snooze');
    });

    // Expenses Tracker / Budgeting
    Route::middleware(['verified', 'onboarding'])->prefix('expenses')->name('expenses.')->group(function () {
        // Landing/Intro
        Route::get('/', [ExpensesController::class, 'index'])->name('index');
        Route::get('/intro', [ExpensesController::class, 'intro'])->name('intro');

        // Budget Setup Wizard
        Route::get('/budget/create', [ExpensesController::class, 'createBudget'])->name('budget.create');
        Route::post('/budget/create', [ExpensesController::class, 'storeBudget'])->name('budget.store');
        Route::get('/budget/{budget}/edit', [ExpensesController::class, 'editBudget'])->name('budget.edit');
        Route::put('/budget/{budget}', [ExpensesController::class, 'updateBudget'])->name('budget.update');
        Route::delete('/budget/{budget}', [ExpensesController::class, 'deleteBudget'])->name('budget.delete');

        // Categories/Envelopes (uses session-selected budget)
        Route::get('/categories', [ExpensesController::class, 'categories'])->name('categories');
        Route::post('/categories', [ExpensesController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [ExpensesController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [ExpensesController::class, 'deleteCategory'])->name('categories.delete');
        Route::post('/categories/reorder', [ExpensesController::class, 'reorderCategories'])->name('categories.reorder');

        // Transactions
        Route::get('/transactions', [ExpensesController::class, 'transactions'])->name('transactions');
        Route::get('/transactions/create', [ExpensesController::class, 'createTransaction'])->name('transactions.create');
        Route::get('/transactions/{transaction}', [ExpensesController::class, 'showTransaction'])->name('transactions.show');
        Route::post('/transactions', [ExpensesController::class, 'storeTransaction'])->name('transactions.store');
        Route::put('/transactions/{transaction}', [ExpensesController::class, 'updateTransaction'])->name('transactions.update');
        Route::delete('/transactions/{transaction}', [ExpensesController::class, 'deleteTransaction'])->name('transactions.delete');
        Route::delete('/transactions/{transaction}/receipt', [ExpensesController::class, 'deleteReceipt'])->name('transactions.receipt.delete');

        // CSV Import
        Route::get('/import', [ExpensesController::class, 'importForm'])->name('import');
        Route::post('/import/upload', [ExpensesController::class, 'uploadCsv'])->name('import.upload');
        Route::post('/import/map', [ExpensesController::class, 'mapColumns'])->name('import.map');
        Route::post('/import/process', [ExpensesController::class, 'processImport'])->name('import.process');

        // Sharing (uses session-selected budget)
        Route::get('/share', [ExpensesController::class, 'shareForm'])->name('share');
        Route::post('/share', [ExpensesController::class, 'shareWith'])->name('share.store');
        Route::delete('/share/{share}', [ExpensesController::class, 'removeShare'])->name('share.delete');

        // Dashboard & Reports
        Route::get('/dashboard', [ExpensesController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports', [ExpensesController::class, 'reports'])->name('reports');
        Route::get('/reports/export', [ExpensesController::class, 'exportReport'])->name('reports.export');

        // Alerts
        Route::get('/alerts', [ExpensesController::class, 'alerts'])->name('alerts');
        Route::post('/alerts', [ExpensesController::class, 'storeAlert'])->name('alerts.store');
        Route::delete('/alerts/{alert}', [ExpensesController::class, 'deleteAlert'])->name('alerts.delete');

        // Payment Requests (Co-Parenting)
        Route::get('/payment-requests', [ExpensesController::class, 'paymentRequests'])->name('payment-requests');
        Route::get('/payment-requests/{payment}', [ExpensesController::class, 'showPaymentRequest'])->name('payment-requests.show');
        Route::post('/payment-requests/{payment}/pay', [ExpensesController::class, 'submitPayment'])->name('payment-requests.pay');
        Route::post('/payment-requests/{payment}/decline', [ExpensesController::class, 'declinePayment'])->name('payment-requests.decline');
        Route::post('/payment-requests/{payment}/cancel', [ExpensesController::class, 'cancelPaymentRequest'])->name('payment-requests.cancel');

        // Mode toggle
        Route::post('/enter-mode', [ExpensesController::class, 'enterMode'])->name('enter-mode');
        Route::post('/exit-mode', [ExpensesController::class, 'exitMode'])->name('exit-mode');
    });

    // Journey
    Route::get('/journey', function () {
        return view('pages.journey.index');
    })->middleware(['verified', 'onboarding'])->name('journey.index');

    // People Directory (Personal CRM)
    Route::middleware(['verified', 'onboarding'])->prefix('people')->name('people.')->group(function () {
        Route::get('/', [PersonController::class, 'index'])->name('index');
        Route::get('/create', [PersonController::class, 'create'])->name('create');
        Route::post('/', [PersonController::class, 'store'])->name('store');
        Route::get('/{person}', [PersonController::class, 'show'])->name('show');
        Route::get('/{person}/edit', [PersonController::class, 'edit'])->name('edit');
        Route::put('/{person}', [PersonController::class, 'update'])->name('update');
        Route::delete('/{person}', [PersonController::class, 'destroy'])->name('destroy');

        // Attachments
        Route::delete('/{person}/attachments/{attachment}', [PersonController::class, 'deleteAttachment'])->name('attachments.delete');
        Route::get('/{person}/attachments/{attachment}/download', [PersonController::class, 'downloadAttachment'])->name('attachments.download');
    });

    // Shopping Lists
    Route::middleware(['verified', 'onboarding'])->prefix('shopping')->name('shopping.')->group(function () {
        Route::get('/', [ShoppingListController::class, 'index'])->name('index');
        Route::post('/', [ShoppingListController::class, 'store'])->name('store');
        Route::get('/{shoppingList}', [ShoppingListController::class, 'show'])->name('show');
        Route::get('/{shoppingList}/store-mode', [ShoppingListController::class, 'storeMode'])->name('store-mode');
        Route::put('/{shoppingList}', [ShoppingListController::class, 'update'])->name('update');
        Route::delete('/{shoppingList}', [ShoppingListController::class, 'destroy'])->name('destroy');

        // Items
        Route::post('/{shoppingList}/items', [ShoppingListController::class, 'addItem'])->name('items.store');
        Route::post('/items/{item}/toggle', [ShoppingListController::class, 'toggleItem'])->name('items.toggle');
        Route::put('/items/{item}', [ShoppingListController::class, 'updateItem'])->name('items.update');
        Route::delete('/items/{item}', [ShoppingListController::class, 'deleteItem'])->name('items.destroy');
        Route::post('/{shoppingList}/clear-checked', [ShoppingListController::class, 'clearChecked'])->name('clear-checked');

        // Share & Email
        Route::post('/{shoppingList}/share', [ShoppingListController::class, 'share'])->name('share');
        Route::post('/{shoppingList}/email', [ShoppingListController::class, 'email'])->name('email');

        // Suggestions
        Route::get('/api/suggestions', [ShoppingListController::class, 'suggestions'])->name('suggestions');
    });

    // Pets
    Route::middleware(['verified', 'onboarding'])->prefix('pets')->name('pets.')->group(function () {
        Route::get('/', [PetController::class, 'index'])->name('index');
        Route::get('/create', [PetController::class, 'create'])->name('create');
        Route::post('/', [PetController::class, 'store'])->name('store');
        Route::get('/{pet}', [PetController::class, 'show'])->name('show');
        Route::get('/{pet}/edit', [PetController::class, 'edit'])->name('edit');
        Route::put('/{pet}', [PetController::class, 'update'])->name('update');
        Route::delete('/{pet}', [PetController::class, 'destroy'])->name('destroy');

        // Vaccinations
        Route::post('/{pet}/vaccinations', [PetController::class, 'storeVaccination'])->name('vaccinations.store');
        Route::put('/{pet}/vaccinations/{vaccination}', [PetController::class, 'updateVaccination'])->name('vaccinations.update');
        Route::delete('/{pet}/vaccinations/{vaccination}', [PetController::class, 'destroyVaccination'])->name('vaccinations.destroy');

        // Medications
        Route::post('/{pet}/medications', [PetController::class, 'storeMedication'])->name('medications.store');
        Route::put('/{pet}/medications/{medication}', [PetController::class, 'updateMedication'])->name('medications.update');
        Route::patch('/{pet}/medications/{medication}/toggle', [PetController::class, 'toggleMedication'])->name('medications.toggle');
        Route::delete('/{pet}/medications/{medication}', [PetController::class, 'destroyMedication'])->name('medications.destroy');
    });

    // Journal
    Route::middleware(['verified', 'onboarding'])->prefix('journal')->name('journal.')->group(function () {
        Route::get('/', [JournalController::class, 'index'])->name('index');
        Route::get('/create', [JournalController::class, 'create'])->name('create');
        Route::post('/', [JournalController::class, 'store'])->name('store');
        Route::get('/tags/search', [JournalController::class, 'searchTags'])->name('tags.search');
        Route::get('/{journalEntry}', [JournalController::class, 'show'])->name('show');
        Route::get('/{journalEntry}/edit', [JournalController::class, 'edit'])->name('edit');
        Route::put('/{journalEntry}', [JournalController::class, 'update'])->name('update');
        Route::delete('/{journalEntry}', [JournalController::class, 'destroy'])->name('destroy');
        Route::patch('/{journalEntry}/toggle-pin', [JournalController::class, 'togglePin'])->name('toggle-pin');
        Route::delete('/{journalEntry}/attachments/{attachment}', [JournalController::class, 'destroyAttachment'])->name('attachments.destroy');
    });

    // Settings
    Route::middleware(['verified', 'onboarding'])->prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::post('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
        Route::delete('/sessions/{session}', [SettingsController::class, 'revokeSession'])->name('sessions.revoke');
        Route::post('/sessions/revoke-all', [SettingsController::class, 'revokeAllSessions'])->name('sessions.revoke-all');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
        Route::post('/appearance', [SettingsController::class, 'updateAppearance'])->name('appearance.update');
        Route::post('/privacy', [SettingsController::class, 'updatePrivacy'])->name('privacy.update');
        Route::get('/export-data', [SettingsController::class, 'exportData'])->name('export-data');
        Route::post('/delete-account', [SettingsController::class, 'requestAccountDeletion'])->name('delete-account');

        // Account Recovery Code
        Route::post('/recovery-code/generate', [SettingsController::class, 'generateRecoveryCode'])->name('recovery-code.generate');
        Route::post('/recovery-code', [SettingsController::class, 'saveRecoveryCode'])->name('recovery-code.save');
    });

    // Subscription & Billing
    Route::middleware(['verified', 'onboarding'])->prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/pricing', [SubscriptionController::class, 'pricing'])->name('pricing');
        Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::post('/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/apply-discount', [SubscriptionController::class, 'applyDiscount'])->name('apply-discount');
        Route::post('/checkout-complete', [SubscriptionController::class, 'checkoutComplete'])->name('checkout-complete');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
        Route::post('/billing-cycle', [SubscriptionController::class, 'changeBillingCycle'])->name('billing-cycle');
    });

    // Image Verification (for viewing sensitive documents/images)
    Route::get('/image-verify/status', [ImageVerificationController::class, 'status'])->name('image-verify.status');
    Route::post('/image-verify/send', [ImageVerificationController::class, 'sendCode'])->name('image-verify.send');
    Route::post('/image-verify/verify', [ImageVerificationController::class, 'verify'])->name('image-verify.verify');

    // Onboarding
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/step1', [OnboardingController::class, 'step1']);
    Route::post('/onboarding/step2', [OnboardingController::class, 'step2']);
    Route::post('/onboarding/step3', [OnboardingController::class, 'step3']);
    Route::post('/onboarding/step4', [OnboardingController::class, 'step4']);
    Route::post('/onboarding/step5', [OnboardingController::class, 'step5']);
    Route::post('/onboarding/back', [OnboardingController::class, 'back']);
    Route::post('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
    Route::post('/onboarding/restart', [OnboardingController::class, 'restart'])->name('onboarding.restart');
    Route::post('/onboarding/generate-recovery-codes', [OnboardingController::class, 'generateRecoveryCodes']);
    Route::post('/onboarding/send-phone-code', [OnboardingController::class, 'sendPhoneCode']);
    Route::post('/onboarding/verify-phone-code', [OnboardingController::class, 'verifyPhoneCode']);
    Route::post('/onboarding/generate-2fa-secret', [OnboardingController::class, 'generate2FASecret']);
    Route::post('/onboarding/verify-2fa-code', [OnboardingController::class, 'verify2FACode']);
});

/*
|--------------------------------------------------------------------------
| Email Tracking Routes (No authentication - accessed from email clients)
|--------------------------------------------------------------------------
*/

Route::get('/email/track/open/{token}', [\App\Http\Controllers\EmailTrackingController::class, 'trackOpen'])
    ->name('email.track.open');
Route::get('/email/track/click/{token}', [\App\Http\Controllers\EmailTrackingController::class, 'trackClick'])
    ->name('email.track.click');

/*
|--------------------------------------------------------------------------
| Webhook Routes (No authentication - called by external services)
|--------------------------------------------------------------------------
*/

Route::post('/webhooks/paddle', [SubscriptionController::class, 'handlePaddleWebhook'])
    ->name('webhooks.paddle');

// Alternative webhook URL (if configured differently in Paddle)
Route::post('/paddle/webhook', [SubscriptionController::class, 'handlePaddleWebhook']);
