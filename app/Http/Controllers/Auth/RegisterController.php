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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Controller for user registration.
 */
class RegisterController extends Controller
{
    /**
     * Show registration form.
     */
    public function show()
    {
        return view('auth.register');
    }

    /**
     * Register a new user with email/password.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => ['required', 'confirmed', Password::min(12)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                ],
                // Honeypot fields
                'website_url_hp' => 'max:0',
            ]);

            // Rate limiting
            $key = 'register:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 5)) {
                return response()->json([
                    'error' => 'Too many registration attempts. Please try again later.',
                ], 429);
            }

            RateLimiter::hit($key, 3600);

            $user = DB::transaction(function () use ($request) {
                // Create tenant (Family Circle)
                $tenant = Tenant::create([
                    'name' => explode(' ', $request->name)[0] . "'s Family",
                    'slug' => Str::slug($request->email) . '-' . Str::random(6),
                ]);

                // Create user
                return User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $request->name,
                    'email' => strtolower($request->email),
                    'password' => $request->password,
                    'auth_provider' => User::PROVIDER_EMAIL,
                    'role' => User::ROLE_PARENT,
                ]);
            });

            // Send email verification OTP (wrapped in try-catch to not fail registration)
            try {
                $otp = Otp::generate($user->email, Otp::TYPE_EMAIL_VERIFY);
                $user->notify(new OtpNotification($otp->code, 'verify'));
            } catch (\Exception $e) {
                Log::warning('Failed to send verification email', ['error' => $e->getMessage()]);
            }

            Log::info('New user registered', ['user_id' => $user->id]);

            // Log in the user
            Auth::login($user);

            return response()->json([
                'message' => 'Registration successful. Please verify your email.',
                'redirect' => '/verify-email',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Registration failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'error' => 'Registration failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify email with OTP.
     */
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
                'redirect' => '/dashboard',
            ]);
        }

        if (!Otp::verify($user->email, Otp::TYPE_EMAIL_VERIFY, $request->code)) {
            return response()->json([
                'error' => 'Invalid or expired verification code.',
            ], 401);
        }

        $user->email_verified_at = now();
        $user->save();

        Log::info('Email verified', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Email verified successfully.',
            'redirect' => '/onboarding',
        ]);
    }

    /**
     * Resend email verification OTP.
     */
    public function resendVerification(Request $request)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
            ]);
        }

        // Rate limiting
        $key = 'verify_resend:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Please wait {$seconds} seconds before requesting another code.",
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $otp = Otp::generate($user->email, Otp::TYPE_EMAIL_VERIFY);
        $user->notify(new OtpNotification($otp->code, 'verify'));

        return response()->json([
            'message' => 'Verification code sent.',
        ]);
    }
}
