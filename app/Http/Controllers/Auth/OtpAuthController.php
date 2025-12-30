<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Controller for email OTP (passwordless) authentication.
 */
class OtpAuthController extends Controller
{
    /**
     * Request an OTP code for login.
     */
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower($request->email);

        // Rate limiting: 5 attempts per minute per email
        $key = 'otp_request:' . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Too many attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 60);

        // Generate OTP
        $otp = Otp::generate($email, Otp::TYPE_EMAIL_LOGIN);

        // Send OTP via email
        Notification::route('mail', $email)
            ->notify(new OtpNotification($otp->code, 'login'));

        Log::info('OTP requested for login', ['email' => $email]);

        return response()->json([
            'message' => 'Verification code sent to your email.',
            'expires_in' => Otp::EXPIRY_MINUTES * 60,
        ]);
    }

    /**
     * Verify OTP and login.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower($request->email);
        $code = $request->code;

        // Rate limiting: 10 attempts per 5 minutes per email
        $key = 'otp_verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Too many attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 300);

        // Verify OTP
        if (!Otp::verify($email, Otp::TYPE_EMAIL_LOGIN, $code)) {
            Log::warning('Invalid OTP attempt', ['email' => $email]);

            return response()->json([
                'error' => 'Invalid or expired verification code.',
            ], 401);
        }

        // Clear rate limits on success
        RateLimiter::clear($key);

        // Find or create user
        $user = User::where('email', $email)->first();

        if (!$user) {
            // This is a new user - create account
            $user = $this->createNewUser($email);
        }

        if (!$user->is_active) {
            return response()->json([
                'error' => 'Your account has been deactivated.',
            ], 403);
        }

        // Mark email as verified
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        $user->recordLogin();

        // Check if MFA is required
        if ($user->hasTwoFactorEnabled() || $user->hasSmsMfaEnabled()) {
            session(['mfa_required' => true, 'mfa_user_id' => $user->id]);
            return response()->json([
                'mfa_required' => true,
                'redirect' => '/auth/mfa',
            ]);
        }

        Auth::login($user, true);

        return response()->json([
            'message' => 'Login successful',
            'redirect' => $user->wasRecentlyCreated ? '/onboarding' : '/dashboard',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
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
                'name' => explode('@', $email)[0], // Use email prefix as name
                'email' => $email,
                'email_verified_at' => now(),
                'auth_provider' => User::PROVIDER_EMAIL,
                'role' => User::ROLE_PARENT,
                'password' => bcrypt(Str::random(32)),
            ]);

            Log::info('New user created via OTP', ['user_id' => $user->id]);

            return $user;
        });
    }

    /**
     * Resend OTP code.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        return $this->requestOtp($request);
    }
}
