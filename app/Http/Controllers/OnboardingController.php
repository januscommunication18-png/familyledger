<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    public const TOTAL_STEPS = 5;

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

        return view('onboarding.index', [
            'step' => $tenant->onboarding_step ?? 1,
            'totalSteps' => self::TOTAL_STEPS,
            'goals' => self::GOALS,
            'countries' => self::COUNTRIES,
            'familyTypes' => self::FAMILY_TYPES,
            'roles' => self::ROLES,
            'quickSetup' => self::QUICK_SETUP,
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
                'email' => $user->email,
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
            'name' => 'required|string|max:255',
            'country' => 'required|string|in:' . implode(',', array_keys(self::COUNTRIES)),
            'timezone' => 'required|string|timezone',
            'family_type' => 'nullable|string|in:' . implode(',', array_keys(self::FAMILY_TYPES)),
        ]);

        $tenant = $request->user()->tenant;
        $tenant->update([
            'name' => $request->name,
            'country' => $request->country,
            'timezone' => $request->timezone,
            'family_type' => $request->family_type,
            'onboarding_step' => 3,
        ]);

        Log::info('Onboarding step 2 completed', ['tenant_id' => $tenant->id]);

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

        // If 2FA is enabled
        if ($request->has('enable_2fa')) {
            $user->update(['mfa_enabled' => true]);
        }

        $tenant->update([
            'onboarding_completed' => true,
            'onboarding_step' => self::TOTAL_STEPS,
        ]);

        Log::info('Onboarding completed', ['tenant_id' => $tenant->id, 'user_id' => $user->id]);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your account is all set up.');
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
