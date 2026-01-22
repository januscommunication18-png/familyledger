<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\TenantResource;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * API Controller for authenticated user management.
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
                'email' => 'required|email|max:255',
                'password' => ['required', 'confirmed', Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                ],
                'device_name' => 'required|string|max:255',
            ], [
                'name.regex' => 'Please enter a valid name (e.g., John Snow). Only letters and spaces are allowed.',
            ]);

            $email = strtolower(trim($request->email));

            // Check if email already exists
            $emailHash = hash('sha256', $email);
            if (User::where('email_hash', $emailHash)->exists()) {
                return $this->error('This email is already registered.', 422);
            }

            // Rate limiting
            $key = 'api_register:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                return $this->error("Too many registration attempts. Please try again in {$seconds} seconds.", 429);
            }

            RateLimiter::hit($key, 3600);

            $user = DB::transaction(function () use ($request, $email) {
                // Create tenant
                $tenant = Tenant::create([
                    'name' => explode(' ', $request->name)[0] . "'s Family",
                    'slug' => Str::slug($email) . '-' . Str::random(6),
                ]);

                // Create user
                return User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $request->name,
                    'email' => $email,
                    'password' => $request->password,
                    'auth_provider' => User::PROVIDER_EMAIL,
                    'role' => User::ROLE_PARENT,
                    'email_verified_at' => now(), // Auto-verify for mobile
                ]);
            });

            // Create API token
            $token = $user->createToken($request->device_name)->plainTextToken;

            $user->recordLogin();

            Log::info('API: New user registered', ['user_id' => $user->id]);

            return $this->success([
                'token' => $token,
                'token_type' => 'Bearer',
                'is_new_user' => true,
                'requires_onboarding' => true,
                'user' => new UserResource($user),
                'tenant' => new TenantResource($user->tenant),
            ], 'Registration successful', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $firstError = collect($errors)->flatten()->first();
            return $this->error($firstError, 422);
        } catch (\Exception $e) {
            Log::error('API: Registration failed', ['error' => $e->getMessage()]);
            return $this->error('Registration failed. Please try again.', 500);
        }
    }

    /**
     * Login with email and password.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
        ]);

        $email = strtolower($request->email);

        // Rate limiting
        $key = 'api_login:' . $email . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            Log::channel('security')->info('API login rate limited', [
                'email' => $email,
                'ip' => $request->ip(),
            ]);

            return $this->error("Too many login attempts. Please try again in {$seconds} seconds.", 429);
        }

        RateLimiter::hit($key, 300);

        // Find user by email
        $user = User::findByEmail($email);

        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::channel('security')->info('API login failed', [
                'email' => $email,
                'ip' => $request->ip(),
                'reason' => 'invalid_credentials',
            ]);

            return $this->error('Invalid email or password.', 401);
        }

        // Check if user is active
        if (!$user->is_active) {
            Log::channel('security')->info('API login failed', [
                'email' => $email,
                'ip' => $request->ip(),
                'reason' => 'account_inactive',
            ]);

            return $this->error('Your account has been deactivated.', 403);
        }

        // Clear rate limits on success
        RateLimiter::clear($key);

        // Create API token
        $token = $user->createToken($request->device_name)->plainTextToken;

        $user->recordLogin();

        Log::channel('security')->info('API login successful', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'device' => $request->device_name,
        ]);

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'tenant' => new TenantResource($user->tenant),
        ], 'Login successful');
    }

    /**
     * Get the authenticated user's information.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success([
            'user' => new UserResource($user),
            'tenant' => new TenantResource($user->tenant),
        ]);
    }

    /**
     * Logout the user (revoke current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke the current access token
        $request->user()->currentAccessToken()->delete();

        Log::info('API: User logged out', ['user_id' => $user->id]);

        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Refresh the user's token.
     * Creates a new token and revokes the old one.
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'device_name' => 'required|string|max:255',
        ]);

        $user = $request->user();

        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        // Create a new token
        $token = $user->createToken($request->device_name)->plainTextToken;

        Log::info('API: Token refreshed', ['user_id' => $user->id]);

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Token refreshed successfully');
    }
}
