<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\TenantResource;
use App\Models\Otp;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * API Controller for Email OTP (passwordless) authentication.
 */
class OtpController extends Controller
{
    /**
     * Request an OTP code for login.
     */
    public function request(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower($request->email);

        // Rate limiting: 5 attempts per minute per email
        $key = 'api_otp_request:' . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->error(
                "Too many attempts. Please try again in {$seconds} seconds.",
                429
            );
        }

        RateLimiter::hit($key, 60);

        // Generate OTP
        $otp = Otp::generate($email, Otp::TYPE_EMAIL_LOGIN);

        // Send OTP via email
        Notification::route('mail', $email)
            ->notify(new OtpNotification($otp->code, 'login'));

        Log::info('API: OTP requested for login', ['email' => $email]);

        return $this->success([
            'expires_in' => Otp::EXPIRY_MINUTES * 60,
        ], 'Verification code sent to your email.');
    }

    /**
     * Verify OTP and return authentication token.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6',
            'device_name' => 'required|string|max:255',
        ]);

        $email = strtolower($request->email);
        $code = $request->code;

        // Rate limiting: 10 attempts per 5 minutes per email
        $key = 'api_otp_verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->error(
                "Too many attempts. Please try again in {$seconds} seconds.",
                429
            );
        }

        RateLimiter::hit($key, 300);

        // Verify OTP
        if (!Otp::verify($email, Otp::TYPE_EMAIL_LOGIN, $code)) {
            Log::warning('API: Invalid OTP attempt', ['email' => $email]);
            return $this->error('Invalid or expired verification code.', 401);
        }

        // Clear rate limits on success
        RateLimiter::clear($key);

        // Find or create user
        $user = User::findByEmail($email);
        $isNewUser = false;

        if (!$user) {
            $user = $this->createNewUser($email);
            $isNewUser = true;
        }

        if (!$user->is_active) {
            return $this->forbidden('Your account has been deactivated.');
        }

        // Mark email as verified
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        $user->recordLogin();

        // Create Sanctum token
        $token = $user->createToken($request->device_name)->plainTextToken;

        Log::info('API: OTP login successful', ['user_id' => $user->id]);

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
     * Resend OTP code.
     */
    public function resend(Request $request): JsonResponse
    {
        return $this->request($request);
    }

    /**
     * Create a new user from email OTP signup.
     */
    protected function createNewUser(string $email): User
    {
        return DB::transaction(function () use ($email) {
            // Create tenant (Family Circle)
            $tenant = Tenant::create([
                'name' => 'My Family',
                'slug' => Str::slug($email) . '-' . Str::random(6),
            ]);

            // Create user
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => explode('@', $email)[0],
                'email' => $email,
                'email_verified_at' => now(),
                'auth_provider' => User::PROVIDER_EMAIL,
                'role' => User::ROLE_PARENT,
                'password' => bcrypt(Str::random(32)),
            ]);

            Log::info('API: New user created via OTP', ['user_id' => $user->id]);

            return $user;
        });
    }
}
