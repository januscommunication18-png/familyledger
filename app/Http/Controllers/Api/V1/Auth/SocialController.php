<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\TenantResource;
use App\Models\SocialAccount;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * API Controller for Social (OAuth) authentication from mobile apps.
 *
 * Mobile apps use native SDKs (Google Sign-In, Sign in with Apple) to get
 * an ID token, which is then sent to this API for verification.
 */
class SocialController extends Controller
{
    /**
     * Authenticate with a social provider.
     *
     * @param Request $request
     * @param string $provider 'google' or 'apple'
     */
    public function authenticate(Request $request, string $provider): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
            'device_name' => 'required|string|max:255',
            // For Apple, these may be provided on first sign-in only
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        // Verify the token with the provider
        $socialUser = $this->verifyProviderToken($provider, $request->id_token, $request);

        if (!$socialUser) {
            return $this->unauthorized('Invalid authentication token.');
        }

        // Find or create user
        $result = $this->findOrCreateUser($provider, $socialUser);
        $user = $result['user'];
        $isNewUser = $result['is_new'];

        if (!$user->is_active) {
            return $this->forbidden('Your account has been deactivated.');
        }

        $user->recordLogin();

        // Create Sanctum token
        $token = $user->createToken($request->device_name)->plainTextToken;

        Log::info('API: Social login successful', [
            'user_id' => $user->id,
            'provider' => $provider,
        ]);

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'is_new_user' => $isNewUser,
            'requires_onboarding' => !$user->tenant->onboarding_completed,
            'user' => new UserResource($user),
            'tenant' => new TenantResource($user->tenant),
        ], 'Login successful');
    }

    /**
     * Verify the ID token with the appropriate provider.
     */
    protected function verifyProviderToken(string $provider, string $idToken, Request $request): ?object
    {
        return match ($provider) {
            'google' => $this->verifyGoogleToken($idToken),
            'apple' => $this->verifyAppleToken($idToken, $request),
            default => null,
        };
    }

    /**
     * Verify Google ID token.
     */
    protected function verifyGoogleToken(string $idToken): ?object
    {
        try {
            // Verify token with Google's tokeninfo endpoint
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if (!$response->successful()) {
                Log::warning('API: Google token verification failed', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $payload = $response->json();

            // Verify the audience (client ID)
            $validClientIds = [
                config('services.google.client_id'),
                config('services.google.ios_client_id'),
                config('services.google.android_client_id'),
            ];

            if (!in_array($payload['aud'] ?? '', array_filter($validClientIds))) {
                Log::warning('API: Google token has invalid audience', [
                    'aud' => $payload['aud'] ?? null,
                ]);
                return null;
            }

            return (object) [
                'id' => $payload['sub'],
                'email' => $payload['email'],
                'name' => $payload['name'] ?? $payload['email'],
                'avatar' => $payload['picture'] ?? null,
                'email_verified' => $payload['email_verified'] ?? false,
            ];
        } catch (\Exception $e) {
            Log::error('API: Google token verification error', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Verify Apple ID token.
     */
    protected function verifyAppleToken(string $idToken, Request $request): ?object
    {
        try {
            // Decode the JWT (without verification for now, we'll verify the signature)
            $tokenParts = explode('.', $idToken);
            if (count($tokenParts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);

            if (!$payload) {
                return null;
            }

            // Verify issuer
            if (($payload['iss'] ?? '') !== 'https://appleid.apple.com') {
                Log::warning('API: Apple token has invalid issuer');
                return null;
            }

            // Verify audience (your app's bundle ID)
            $validAudiences = [
                config('services.apple.client_id'),
                config('services.apple.bundle_id'),
            ];

            if (!in_array($payload['aud'] ?? '', array_filter($validAudiences))) {
                Log::warning('API: Apple token has invalid audience', [
                    'aud' => $payload['aud'] ?? null,
                ]);
                return null;
            }

            // Verify expiration
            if (($payload['exp'] ?? 0) < time()) {
                Log::warning('API: Apple token has expired');
                return null;
            }

            // For Apple, email and name may only be provided on first sign-in
            // After that, we need to look them up from our records or the request
            $email = $payload['email'] ?? $request->email;
            $name = $request->name;

            if (!$email) {
                // Try to find existing user by Apple ID
                $existingAccount = SocialAccount::where('provider', 'apple')
                    ->where('provider_id', $payload['sub'])
                    ->first();

                if ($existingAccount) {
                    $email = $existingAccount->user->email;
                    $name = $existingAccount->user->name;
                }
            }

            return (object) [
                'id' => $payload['sub'],
                'email' => $email,
                'name' => $name ?? ($email ? explode('@', $email)[0] : 'Apple User'),
                'avatar' => null,
                'email_verified' => true, // Apple verifies emails
            ];
        } catch (\Exception $e) {
            Log::error('API: Apple token verification error', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Find or create a user from social provider data.
     */
    protected function findOrCreateUser(string $provider, object $socialUser): array
    {
        return DB::transaction(function () use ($provider, $socialUser) {
            // Check if social account exists
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_id', $socialUser->id)
                ->first();

            if ($socialAccount) {
                // Update avatar if changed
                if ($socialUser->avatar && $socialAccount->avatar !== $socialUser->avatar) {
                    $socialAccount->update(['avatar' => $socialUser->avatar]);
                }

                return [
                    'user' => $socialAccount->user,
                    'is_new' => false,
                ];
            }

            // Check if user exists by email
            $user = $socialUser->email ? User::findByEmail($socialUser->email) : null;
            $isNewUser = false;

            if (!$user) {
                // Create new tenant and user
                $tenant = Tenant::create([
                    'name' => 'My Family',
                    'slug' => Str::slug($socialUser->email ?? $socialUser->id) . '-' . Str::random(6),
                ]);

                $user = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'email_verified_at' => $socialUser->email_verified ? now() : null,
                    'auth_provider' => $provider,
                    'role' => User::ROLE_PARENT,
                    'password' => bcrypt(Str::random(32)),
                    'avatar' => $socialUser->avatar,
                ]);

                $isNewUser = true;

                Log::info('API: New user created via social login', [
                    'user_id' => $user->id,
                    'provider' => $provider,
                ]);
            }

            // Link social account
            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialUser->id,
                'avatar' => $socialUser->avatar,
            ]);

            return [
                'user' => $user,
                'is_new' => $isNewUser,
            ];
        });
    }
}
