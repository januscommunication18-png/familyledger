<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class OnboardingController extends Controller
{
    public const TOTAL_STEPS = 6;

    public const GOALS = [
        'documents' => [
            'title' => 'Manage family documents',
            'description' => 'Store and organize important papers',
        ],
        'coparenting' => [
            'title' => 'Co-parenting coordination',
            'description' => 'Shared schedules and communication',
        ],
        'household' => [
            'title' => 'Household organization',
            'description' => 'Lists, tasks, and family coordination',
        ],
        'financial' => [
            'title' => 'Financial and expense tracking',
            'description' => 'Budgets, bills and shared expenses',
        ],
        'all' => [
            'title' => 'All of the above',
            'description' => 'Complete family management solution',
        ],
    ];

    public const COUNTRIES = [
        'US' => 'United States',
        'GB' => 'United Kingdom',
        'CA' => 'Canada',
        'AU' => 'Australia',
        'DE' => 'Germany',
        'FR' => 'France',
        'OTHER' => 'Other',
    ];

    public const COUNTRY_CODES = [
        '+1' => 'US/CA (+1)',
        '+44' => 'UK (+44)',
        '+61' => 'AU (+61)',
        '+49' => 'DE (+49)',
        '+33' => 'FR (+33)',
        '+91' => 'IN (+91)',
        '+86' => 'CN (+86)',
        '+81' => 'JP (+81)',
        '+82' => 'KR (+82)',
        '+52' => 'MX (+52)',
        '+55' => 'BR (+55)',
        '+34' => 'ES (+34)',
        '+39' => 'IT (+39)',
        '+31' => 'NL (+31)',
        '+7' => 'RU (+7)',
        '+65' => 'SG (+65)',
        '+971' => 'UAE (+971)',
        '+966' => 'SA (+966)',
        '+27' => 'ZA (+27)',
        '+234' => 'NG (+234)',
    ];

    public const FAMILY_TYPES = [
        'married' => 'Married / Partnered',
        'coparenting' => 'Co-parenting',
        'single_parent' => 'Single Parent',
        'multi_generation' => 'Multi-generation household',
    ];

    public const ROLES = [
        'parent' => [
            'title' => 'Parent / Primary Guardian',
            'description' => 'Full access to all features',
        ],
        'coparent' => [
            'title' => 'Co-parent',
            'description' => 'Shared access with coordinated permissions',
        ],
        'guardian' => [
            'title' => 'Guardian',
            'description' => 'Extended family or legal guardian',
        ],
        'family_member' => [
            'title' => 'Family Member',
            'description' => 'Limited access to shared information',
        ],
        'advisor' => [
            'title' => 'Advisor',
            'description' => 'CPA, Lawyer, Caregiver, or other professional',
        ],
    ];

    public const QUICK_SETUP = [
        'documents' => [
            'title' => 'Upload important documents',
            'description' => 'Birth certificates, insurance, legal papers',
        ],
        'expenses' => [
            'title' => 'Track shared expenses',
            'description' => 'Bills, budgets, and reimbursements',
        ],
        'lists' => [
            'title' => 'Create family lists',
            'description' => 'Shopping, to-dos, meal planning',
        ],
        'medical' => [
            'title' => 'Add medical / insurance info',
            'description' => 'Health records, providers, medications',
        ],
    ];

    public function show(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        if ($tenant->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        // Handle payment success redirect from Paddle
        if ($request->has('payment') && $request->payment === 'success') {
            $planId = $request->input('plan_id');
            $billingCycle = $request->input('billing_cycle', 'monthly');

            if ($planId) {
                $plan = \App\Models\PackagePlan::find($planId);
                if ($plan && $plan->isPaid()) {
                    $tenant->update([
                        'package_plan_id' => $plan->id,
                        'subscription_tier' => 'paid',
                        'billing_cycle' => $billingCycle,
                        'subscription_expires_at' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                        'onboarding_completed' => true,
                        'onboarding_step' => self::TOTAL_STEPS,
                    ]);

                    Log::info('Onboarding completed via Paddle payment redirect', [
                        'tenant_id' => $tenant->id,
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'billing_cycle' => $billingCycle,
                    ]);

                    return redirect()->route('dashboard')->with('success', 'Welcome! Your premium subscription is now active.');
                }
            }
        }

        // Split name into first and last if not already set
        $nameParts = explode(' ', $user->name, 2);
        $firstName = $user->first_name ?? $nameParts[0] ?? '';
        $lastName = $user->last_name ?? ($nameParts[1] ?? '');

        // Get plans for billing step
        $plans = \App\Models\PackagePlan::active()->ordered()->get();

        return view('onboarding.index', [
            'step' => $tenant->onboarding_step ?? 1,
            'totalSteps' => self::TOTAL_STEPS,
            'goals' => self::GOALS,
            'countries' => self::COUNTRIES,
            'countryCodes' => self::COUNTRY_CODES,
            'familyTypes' => self::FAMILY_TYPES,
            'roles' => self::ROLES,
            'quickSetup' => self::QUICK_SETUP,
            'plans' => $plans,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'country' => $tenant->country,
                'timezone' => $tenant->timezone,
                'family_type' => $tenant->family_type,
                'goals' => $tenant->goals ?? [],
                'quick_setup' => $tenant->quick_setup ?? [],
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'backup_email' => $user->backup_email,
                'country_code' => $user->country_code,
                'phone' => $user->phone,
                'phone_verified' => $user->phone_verified_at ? '1' : '0',
                'role' => $user->role,
            ],
            'timezones' => $this->getTimezones(),
        ]);
    }

    public function step1(Request $request)
    {
        $request->validate([
            'goals' => 'required|array|min:1',
            'goals.*' => 'string|in:' . implode(',', array_keys(self::GOALS)),
        ]);

        $tenant = $request->user()->tenant;

        $tenant->update([
            'goals' => $request->goals,
            'onboarding_step' => 2,
        ]);

        Log::info('Onboarding step 1 completed', ['tenant_id' => $tenant->id]);

        return redirect()->route('onboarding');
    }

    public function step2(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'backup_email' => 'nullable|email|max:255',
            'country_code' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'country' => 'required|string|in:' . implode(',', array_keys(self::COUNTRIES)),
            'timezone' => 'required|string|timezone',
            'family_type' => 'nullable|string|in:' . implode(',', array_keys(self::FAMILY_TYPES)),
        ]);

        $user = $request->user();

        // DEBUG: Log before update
        Log::info('Onboarding step 2 - Before update', [
            'user_id' => $user->id,
            'input_first_name' => $request->first_name,
            'input_last_name' => $request->last_name,
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'name' => $request->first_name . ' ' . $request->last_name,
            'backup_email' => $request->backup_email,
            'country_code' => $request->country_code,
            'phone' => $request->phone,
        ]);

        // DEBUG: Check raw database value after save
        $rawUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->first();
        Log::info('Onboarding step 2 - After update (raw DB)', [
            'user_id' => $user->id,
            'raw_first_name' => $rawUser->first_name ?? 'NULL',
            'raw_last_name' => $rawUser->last_name ?? 'NULL',
            'is_encrypted' => str_starts_with($rawUser->first_name ?? '', 'eyJ') ? 'YES' : 'NO',
        ]);

        $tenant = $user->tenant;
        $tenant->update([
            'name' => $request->name,
            'country' => $request->country,
            'timezone' => $request->timezone,
            'family_type' => $request->family_type,
            'onboarding_step' => 3,
        ]);

        Log::info('Onboarding step 2 completed', ['tenant_id' => $tenant->id, 'user_id' => $user->id]);

        return redirect()->route('onboarding');
    }

    public function step3(Request $request)
    {
        $request->validate([
            'role' => 'required|string|in:' . implode(',', array_keys(self::ROLES)),
        ]);

        $user = $request->user();
        $user->update(['role' => $request->role]);
        $user->tenant->update(['onboarding_step' => 4]);

        Log::info('Onboarding step 3 completed', ['user_id' => $user->id, 'role' => $request->role]);

        return redirect()->route('onboarding');
    }

    public function step4(Request $request)
    {
        $request->validate([
            'quick_setup' => 'required|array|min:1',
            'quick_setup.*' => 'string|in:' . implode(',', array_keys(self::QUICK_SETUP)),
        ]);

        $tenant = $request->user()->tenant;
        $tenant->update([
            'quick_setup' => $request->quick_setup,
            'onboarding_step' => 5,
        ]);

        Log::info('Onboarding step 4 completed', ['tenant_id' => $tenant->id]);

        return redirect()->route('onboarding');
    }

    public function step5(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Store notification preferences
        $tenant->setSetting('email_notifications', $request->has('email_notifications'));
        $tenant->save();

        // Update user MFA settings
        $user->update([
            'mfa_enabled' => $request->has('enable_2fa'),
            'phone_2fa_enabled' => $request->has('enable_phone_2fa') && !empty($user->phone),
            'mfa_method' => $request->has('enable_phone_2fa') ? 'sms' : ($request->has('enable_2fa') ? 'authenticator' : null),
        ]);

        $tenant->update([
            'onboarding_step' => 6,
        ]);

        Log::info('Onboarding step 5 completed', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'mfa_enabled' => $user->mfa_enabled,
            'phone_2fa_enabled' => $user->phone_2fa_enabled,
        ]);

        return redirect()->route('onboarding');
    }

    public function step6(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $selectedPlanId = $request->input('plan_id');
        $billingCycle = $request->input('billing_cycle', 'monthly');

        // Get the selected plan
        $plan = \App\Models\PackagePlan::find($selectedPlanId);

        if ($plan) {
            // If free plan, just assign it and complete
            if ($plan->isFree()) {
                $tenant->update([
                    'package_plan_id' => $plan->id,
                    'subscription_tier' => 'free',
                    'billing_cycle' => null,
                    'onboarding_completed' => true,
                    'onboarding_step' => self::TOTAL_STEPS,
                ]);

                Log::info('Onboarding completed with free plan', [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ]);
            } else {
                // For paid plans, the payment will be handled via JavaScript/Paddle
                // This endpoint is called after successful payment
                $tenant->update([
                    'package_plan_id' => $plan->id,
                    'subscription_tier' => 'paid',
                    'billing_cycle' => $billingCycle,
                    'onboarding_completed' => true,
                    'onboarding_step' => self::TOTAL_STEPS,
                ]);

                Log::info('Onboarding completed with paid plan', [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'billing_cycle' => $billingCycle,
                ]);
            }
        } else {
            // No plan selected, use default free plan
            $freePlan = \App\Models\PackagePlan::where('type', 'free')->active()->first();
            $tenant->update([
                'package_plan_id' => $freePlan?->id,
                'subscription_tier' => 'free',
                'onboarding_completed' => true,
                'onboarding_step' => self::TOTAL_STEPS,
            ]);

            Log::info('Onboarding completed with default free plan', [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
            ]);
        }

        // Check if there's a pending collaborator invite to accept
        $pendingInviteRedirect = session('pending_invite_redirect');
        if ($pendingInviteRedirect) {
            session()->forget('pending_invite_redirect');
            Log::info('Redirecting to pending invite after onboarding', ['user_id' => $user->id, 'redirect' => $pendingInviteRedirect]);
            return redirect($pendingInviteRedirect)->with('success', 'Account setup complete! You can now accept the invitation.');
        }

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account is all set up.');
    }

    public function generateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        // Generate 8 recovery codes
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4))) . '-' . strtoupper(bin2hex(random_bytes(4)));
        }

        // Store hashed codes in database
        $hashedCodes = array_map(fn($code) => [
            'code' => hash('sha256', $code),
            'used' => false,
        ], $codes);

        $user->update(['recovery_codes' => $hashedCodes]);

        Log::info('Recovery codes generated', ['user_id' => $user->id]);

        // Return plain codes to user (only time they'll see them)
        return response()->json(['codes' => $codes]);
    }

    public function sendPhoneCode(Request $request)
    {
        $user = $request->user();

        if (empty($user->phone)) {
            return response()->json(['success' => false, 'message' => 'No phone number on file.']);
        }

        // Generate 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store code in session with expiry (10 minutes)
        session([
            'phone_verification_code' => $code,
            'phone_verification_expires' => now()->addMinutes(10),
        ]);

        // Send via Twilio
        $twilioService = app(\App\Services\TwilioService::class);

        if ($twilioService->isConfigured()) {
            $phoneNumber = $user->country_code . $user->phone;
            $sent = $twilioService->sendVerificationCode($phoneNumber, $code);

            if (!$sent) {
                return response()->json(['success' => false, 'message' => 'Failed to send SMS. Please try again.']);
            }
            Log::info('Phone verification code sent via Twilio', ['user_id' => $user->id]);
        } else {
            // For development without Twilio configured, log the code
            Log::info('Phone verification code (Twilio not configured)', [
                'code' => $code,
                'user_id' => $user->id,
                'phone' => $user->country_code . $user->phone
            ]);
        }

        return response()->json(['success' => true, 'dev_mode' => !$twilioService->isConfigured()]);
    }

    public function verifyPhoneCode(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);

        $storedCode = session('phone_verification_code');
        $expiresAt = session('phone_verification_expires');

        if (!$storedCode || !$expiresAt) {
            return response()->json(['success' => false, 'message' => 'No verification code found. Please request a new one.']);
        }

        if (now()->gt($expiresAt)) {
            session()->forget(['phone_verification_code', 'phone_verification_expires']);
            return response()->json(['success' => false, 'message' => 'Code expired. Please request a new one.']);
        }

        if ($request->code !== $storedCode) {
            return response()->json(['success' => false, 'message' => 'Invalid code. Please try again.']);
        }

        // Mark phone as verified
        $user = $request->user();
        $user->update(['phone_verified_at' => now()]);

        // Clear session
        session()->forget(['phone_verification_code', 'phone_verification_expires']);

        Log::info('Phone verified', ['user_id' => $user->id]);

        return response()->json(['success' => true]);
    }

    public function generate2FASecret(Request $request)
    {
        $user = $request->user();
        $google2fa = new Google2FA();

        // Generate secret
        $secret = $google2fa->generateSecretKey();

        // Store temporarily in session
        session(['2fa_secret' => $secret]);

        // Generate QR code
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'FamilyLedger'),
            $user->email,
            $secret
        );

        // Generate SVG QR code
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return response()->json([
            'success' => true,
            'secret' => $secret,
            'qr_code' => $qrCodeSvg,
        ]);
    }

    public function verify2FACode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'secret' => 'required|string',
        ]);

        $google2fa = new Google2FA();
        $secret = $request->secret;
        $code = $request->code;

        // Verify the code
        $valid = $google2fa->verifyKey($secret, $code);

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid code. Please check and try again.',
            ]);
        }

        // Save the secret to user
        $user = $request->user();
        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_confirmed_at' => now(),
            'mfa_enabled' => true,
            'mfa_method' => 'authenticator',
        ]);

        // Clear session
        session()->forget('2fa_secret');

        Log::info('2FA authenticator enabled', ['user_id' => $user->id]);

        return response()->json(['success' => true]);
    }

    public function back(Request $request)
    {
        $tenant = $request->user()->tenant;
        $currentStep = $tenant->onboarding_step;

        if ($currentStep > 1) {
            $tenant->update(['onboarding_step' => $currentStep - 1]);
        }

        return redirect()->route('onboarding');
    }

    /**
     * Skip onboarding and mark as completed.
     */
    public function skip(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $tenant->update([
            'onboarding_completed' => true,
            'onboarding_skipped' => true,
        ]);

        Log::info('Onboarding skipped', ['tenant_id' => $tenant->id, 'user_id' => $user->id]);

        return redirect()->route('dashboard')->with('info', 'You can complete your profile setup anytime from Settings.');
    }

    /**
     * Restart onboarding (for owners who skipped).
     */
    public function restart(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Only allow owner (first user) to restart onboarding
        $owner = \App\Models\User::where('tenant_id', $tenant->id)->orderBy('created_at')->first();
        if (!$owner || $owner->id !== $user->id) {
            return redirect()->route('settings.index')->with('error', 'Only the account owner can restart onboarding.');
        }

        $tenant->update([
            'onboarding_completed' => false,
            'onboarding_skipped' => false,
            'onboarding_step' => 1,
        ]);

        Log::info('Onboarding restarted', ['tenant_id' => $tenant->id, 'user_id' => $user->id]);

        return redirect()->route('onboarding');
    }

    private function getTimezones(): array
    {
        return [
            'United States' => [
                'America/New_York',
                'America/Chicago',
                'America/Denver',
                'America/Phoenix',
                'America/Los_Angeles',
                'America/Anchorage',
                'Pacific/Honolulu',
            ],
            'United Kingdom' => [
                'Europe/London',
            ],
            'Canada' => [
                'America/Toronto',
                'America/Vancouver',
                'America/Edmonton',
                'America/Winnipeg',
                'America/Halifax',
                'America/St_Johns',
            ],
            'Australia' => [
                'Australia/Sydney',
                'Australia/Melbourne',
                'Australia/Brisbane',
                'Australia/Perth',
                'Australia/Adelaide',
                'Australia/Darwin',
                'Australia/Hobart',
            ],
            'Germany' => [
                'Europe/Berlin',
            ],
            'France' => [
                'Europe/Paris',
            ],
        ];
    }
}
