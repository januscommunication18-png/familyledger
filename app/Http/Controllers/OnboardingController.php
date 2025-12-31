<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        'IN' => 'India',
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
        // Skip if user clicked skip button
        if ($request->has('skip')) {
            $request->user()->tenant->update(['onboarding_step' => 5]);
            return redirect()->route('onboarding');
        }

        $request->validate([
            'members' => 'nullable|array',
            'members.*.email' => 'nullable|email',
            'members.*.phone' => 'nullable|string',
            'members.*.role' => 'nullable|string|in:' . implode(',', array_keys(self::ROLES)),
            'members.*.relationship' => 'nullable|string|max:100',
        ]);

        $user = $request->user();
        $tenant = $user->tenant;

        if ($request->has('members')) {
            foreach ($request->members as $member) {
                // Only create invitation if email and role are provided
                if (!empty($member['email']) && !empty($member['role'])) {
                    Invitation::create([
                        'tenant_id' => $tenant->id,
                        'invited_by' => $user->id,
                        'email' => $member['email'],
                        'phone' => $member['phone'] ?? null,
                        'role' => $member['role'],
                        'relationship' => $member['relationship'] ?? null,
                    ]);
                }
            }
        }

        $tenant->update(['onboarding_step' => 5]);

        Log::info('Onboarding step 4 completed', ['tenant_id' => $tenant->id]);

        return redirect()->route('onboarding');
    }

    public function step5(Request $request)
    {
        $request->validate([
            'quick_setup' => 'required|array|min:1',
            'quick_setup.*' => 'string|in:' . implode(',', array_keys(self::QUICK_SETUP)),
        ]);

        $tenant = $request->user()->tenant;
        $tenant->update([
            'quick_setup' => $request->quick_setup,
            'onboarding_step' => 6,
        ]);

        Log::info('Onboarding step 5 completed', ['tenant_id' => $tenant->id]);

        return redirect()->route('onboarding');
    }

    public function step6(Request $request)
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
        $timezones = [];
        $regions = [
            'America' => \DateTimeZone::AMERICA,
            'Europe' => \DateTimeZone::EUROPE,
            'Asia' => \DateTimeZone::ASIA,
            'Pacific' => \DateTimeZone::PACIFIC,
            'Australia' => \DateTimeZone::AUSTRALIA,
            'Africa' => \DateTimeZone::AFRICA,
        ];

        foreach ($regions as $region => $mask) {
            $zones = \DateTimeZone::listIdentifiers($mask);
            foreach ($zones as $zone) {
                $timezones[$region][] = $zone;
            }
        }

        return $timezones;
    }
}
