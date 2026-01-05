<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for onboarding flow.
 */
class OnboardingController extends Controller
{
    /**
     * Get the current onboarding status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return $this->success([
            'completed' => (bool) $tenant->onboarding_completed,
            'current_step' => $tenant->onboarding_step ?? 1,
            'goals' => $tenant->goals ?? [],
            'data' => $tenant->data ?? [],
            'tenant' => new TenantResource($tenant),
        ]);
    }

    /**
     * Save data for a specific onboarding step.
     */
    public function saveStep(Request $request, int $step): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        // Validate based on step
        $validatedData = $this->validateStep($request, $step);

        if ($validatedData === false) {
            return $this->validationError(['step' => 'Invalid step data']);
        }

        // Get existing data or empty array
        $existingData = $tenant->data ?? [];

        // Merge step data
        $stepKey = "step_{$step}";
        $existingData[$stepKey] = $validatedData;

        // Handle specific steps
        switch ($step) {
            case 1: // Goals
                $tenant->goals = $validatedData['goals'] ?? [];
                break;

            case 2: // Profile
                if (isset($validatedData['name'])) {
                    $tenant->name = $validatedData['name'];
                }
                if (isset($validatedData['country'])) {
                    $tenant->country = $validatedData['country'];
                }
                if (isset($validatedData['timezone'])) {
                    $tenant->timezone = $validatedData['timezone'];
                }
                // Update user profile
                $user->update([
                    'first_name' => $validatedData['first_name'] ?? $user->first_name,
                    'last_name' => $validatedData['last_name'] ?? $user->last_name,
                    'phone' => $validatedData['phone'] ?? $user->phone,
                    'country_code' => $validatedData['country_code'] ?? $user->country_code,
                ]);
                break;

            case 3: // Role
                if (isset($validatedData['role'])) {
                    $user->update(['role' => $validatedData['role']]);
                }
                break;

            case 4: // Quick Setup (features to enable)
                // Store selected features
                break;

            case 5: // Security
                // Handle 2FA setup if needed
                break;
        }

        // Update tenant
        $tenant->data = $existingData;
        $tenant->onboarding_step = $step + 1;
        $tenant->save();

        Log::info('API: Onboarding step saved', [
            'user_id' => $user->id,
            'step' => $step,
        ]);

        return $this->success([
            'step' => $step,
            'next_step' => $step + 1,
            'data' => $validatedData,
        ], "Step {$step} saved successfully");
    }

    /**
     * Complete the onboarding process.
     */
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $tenant->update([
            'onboarding_completed' => true,
            'onboarding_step' => 6, // Mark as past all steps
        ]);

        Log::info('API: Onboarding completed', ['user_id' => $user->id]);

        return $this->success([
            'completed' => true,
            'redirect' => '/dashboard',
            'tenant' => new TenantResource($tenant->fresh()),
        ], 'Onboarding completed successfully');
    }

    /**
     * Validate data for a specific step.
     */
    protected function validateStep(Request $request, int $step): array|false
    {
        $rules = match ($step) {
            1 => ['goals' => 'required|array|min:1'],
            2 => [
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'country_code' => 'nullable|string|max:5',
                'name' => 'nullable|string|max:255', // Family circle name
                'country' => 'nullable|string|max:2',
                'timezone' => 'nullable|string|max:100',
            ],
            3 => ['role' => 'nullable|string|in:parent,coparent,guardian,advisor,viewer'],
            4 => ['features' => 'nullable|array'],
            5 => [
                'enable_2fa' => 'nullable|boolean',
                'enable_notifications' => 'nullable|boolean',
            ],
            default => [],
        };

        try {
            return $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return false;
        }
    }
}
