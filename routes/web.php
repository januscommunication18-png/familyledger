<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\OtpAuthController;
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
use App\Http\Controllers\SettingsController;
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
    Route::post('/auth/mfa/sms/send', [MfaController::class, 'sendSmsCode']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Protected by Security Code)
|--------------------------------------------------------------------------
*/

Route::middleware(['security.code', 'auth'])->group(function () {
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
    Route::post('/settings/mfa/disable', [MfaController::class, 'disableMfa']);

    // Unlink Social Account
    Route::delete('/settings/social/{provider}', [SocialAuthController::class, 'unlink'])
        ->where('provider', 'google|apple|facebook');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware('verified')->name('dashboard');

    // Family Circle
    Route::middleware('verified')->prefix('family-circle')->name('family-circle.')->group(function () {
        Route::get('/', [FamilyCircleController::class, 'index'])->name('index');
        Route::post('/', [FamilyCircleController::class, 'store'])->name('store');
        Route::get('/{familyCircle}', [FamilyCircleController::class, 'show'])->name('show');
        Route::put('/{familyCircle}', [FamilyCircleController::class, 'update'])->name('update');
        Route::delete('/{familyCircle}', [FamilyCircleController::class, 'destroy'])->name('destroy');

        // Family Members
        Route::get('/{familyCircle}/members/create', [FamilyMemberController::class, 'create'])->name('member.create');
        Route::post('/{familyCircle}/members', [FamilyMemberController::class, 'store'])->name('member.store');
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
    });

    // Member Documents (accessible directly via member ID)
    Route::middleware('verified')->prefix('member')->name('member.')->group(function () {
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
        Route::post('/{member}/documents', [MemberDocumentController::class, 'store'])->name('documents.store');
        Route::get('/{member}/documents/{document}', [MemberDocumentController::class, 'show'])->name('documents.show');
        Route::put('/{member}/documents/{document}', [MemberDocumentController::class, 'update'])->name('documents.update');
        Route::delete('/{member}/documents/{document}', [MemberDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::get('/{member}/documents/{document}/image/{type}', [MemberDocumentController::class, 'image'])
            ->where('type', 'front|back')
            ->name('documents.image');
    });

    // Assets
    Route::middleware('verified')->prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::get('/create', [AssetController::class, 'create'])->name('create');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}', [AssetController::class, 'show'])->name('show');
        Route::get('/{asset}/edit', [AssetController::class, 'edit'])->name('edit');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');

        // Document management
        Route::post('/{asset}/documents', [AssetController::class, 'uploadDocument'])->name('documents.upload');
        Route::delete('/{asset}/documents/{document}', [AssetController::class, 'deleteDocument'])->name('documents.destroy');
        Route::get('/{asset}/documents/{document}/download', [AssetController::class, 'downloadDocument'])->name('documents.download');
        Route::get('/{asset}/documents/{document}/view', [AssetController::class, 'viewDocument'])->name('documents.view');
    });

    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->middleware('verified')->name('documents.index');

    // Insurance Policies
    Route::get('/documents/insurance/create', [DocumentController::class, 'createInsurance'])->middleware('verified')->name('documents.insurance.create');
    Route::post('/documents/insurance', [DocumentController::class, 'storeInsurance'])->middleware('verified')->name('documents.insurance.store');
    Route::get('/documents/insurance/{insurance}', [DocumentController::class, 'showInsurance'])->middleware('verified')->name('documents.insurance.show');
    Route::get('/documents/insurance/{insurance}/edit', [DocumentController::class, 'editInsurance'])->middleware('verified')->name('documents.insurance.edit');
    Route::put('/documents/insurance/{insurance}', [DocumentController::class, 'updateInsurance'])->middleware('verified')->name('documents.insurance.update');
    Route::delete('/documents/insurance/{insurance}', [DocumentController::class, 'destroyInsurance'])->middleware('verified')->name('documents.insurance.destroy');
    Route::get('/documents/insurance/{insurance}/card/{type}', [DocumentController::class, 'insuranceCardImage'])->middleware('verified')->name('documents.insurance.card');

    // Tax Returns
    Route::get('/documents/tax-returns/create', [DocumentController::class, 'createTaxReturn'])->middleware('verified')->name('documents.tax-returns.create');
    Route::post('/documents/tax-returns', [DocumentController::class, 'storeTaxReturn'])->middleware('verified')->name('documents.tax-returns.store');
    Route::get('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'showTaxReturn'])->middleware('verified')->name('documents.tax-returns.show');
    Route::get('/documents/tax-returns/{taxReturn}/edit', [DocumentController::class, 'editTaxReturn'])->middleware('verified')->name('documents.tax-returns.edit');
    Route::put('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'updateTaxReturn'])->middleware('verified')->name('documents.tax-returns.update');
    Route::delete('/documents/tax-returns/{taxReturn}', [DocumentController::class, 'destroyTaxReturn'])->middleware('verified')->name('documents.tax-returns.destroy');
    Route::get('/documents/tax-returns/{taxReturn}/download/{type}/{index}', [DocumentController::class, 'downloadTaxReturnFile'])->middleware('verified')->name('documents.tax-returns.download');

    // Tasks (To Do List)
    Route::get('/tasks', function () {
        return view('pages.tasks.index');
    })->middleware('verified')->name('tasks.index');

    // Collaborators
    Route::get('/collaborators', function () {
        return view('pages.collaborators.index');
    })->middleware('verified')->name('collaborators.index');

    // Reminders
    Route::get('/reminders', function () {
        return view('pages.reminders.index');
    })->middleware('verified')->name('reminders.index');

    // Expenses Tracker
    Route::get('/expenses', function () {
        return view('pages.expenses.index');
    })->middleware('verified')->name('expenses.index');

    // Journey
    Route::get('/journey', function () {
        return view('pages.journey.index');
    })->middleware('verified')->name('journey.index');

    // People Directory (Personal CRM)
    Route::middleware('verified')->prefix('people')->name('people.')->group(function () {
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

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('verified')->name('settings.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->middleware('verified')->name('settings.profile.update');

    // Onboarding
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('/onboarding/step1', [OnboardingController::class, 'step1']);
    Route::post('/onboarding/step2', [OnboardingController::class, 'step2']);
    Route::post('/onboarding/step3', [OnboardingController::class, 'step3']);
    Route::post('/onboarding/step4', [OnboardingController::class, 'step4']);
    Route::post('/onboarding/step5', [OnboardingController::class, 'step5']);
    Route::post('/onboarding/step6', [OnboardingController::class, 'step6']);
    Route::post('/onboarding/back', [OnboardingController::class, 'back']);
});
