<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\TenantResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * API Controller for authenticated user management.
 */
class AuthController extends Controller
{
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
