<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Controller for Multi-Factor Authentication (MFA).
 * Supports SMS authentication.
 */
class MfaController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Show MFA verification page.
     */
    public function show(Request $request)
    {
        if (!session('mfa_required')) {
            return redirect('/login');
        }

        $userId = session('mfa_user_id');
        $user = User::find($userId);

        if (!$user) {
            session()->forget(['mfa_required', 'mfa_user_id']);
            return redirect('/login');
        }

        return view('auth.mfa', [
            'method' => $user->mfa_method,
            'phone_last_four' => $user->phone ? substr($user->phone, -4) : null,
        ]);
    }

    /**
     * Send SMS MFA code.
     */
    public function sendSmsCode(Request $request)
    {
        $userId = session('mfa_user_id');
        $user = User::find($userId);

        if (!$user || !$user->phone) {
            return response()->json(['error' => 'MFA not configured'], 400);
        }

        // Rate limiting
        $key = 'mfa_sms:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 120);

        // Generate and send OTP
        $otp = Otp::generate($user->phone, Otp::TYPE_SMS_MFA);

        if (!$this->smsService->sendMfaCode($user->phone, $otp->code)) {
            return response()->json(['error' => 'Failed to send SMS'], 500);
        }

        Log::info('MFA SMS sent', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Verification code sent',
            'phone_last_four' => substr($user->phone, -4),
        ]);
    }

    /**
     * Verify MFA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session('mfa_user_id');
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'Session expired'], 401);
        }

        // Rate limiting
        $key = 'mfa_verify:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            session()->forget(['mfa_required', 'mfa_user_id']);
            return response()->json([
                'error' => 'Too many failed attempts. Please login again.',
            ], 429);
        }

        RateLimiter::hit($key, 300);

        // Verify based on MFA method
        $verified = false;

        if ($user->mfa_method === 'sms') {
            $verified = Otp::verify($user->phone, Otp::TYPE_SMS_MFA, $request->code);
        }

        if (!$verified) {
            Log::warning('Invalid MFA code', ['user_id' => $user->id]);
            return response()->json(['error' => 'Invalid verification code'], 401);
        }

        // Clear session and rate limits
        RateLimiter::clear($key);
        session()->forget(['mfa_required', 'mfa_user_id']);

        // Login user
        Auth::login($user, true);
        $user->recordLogin();

        Log::info('MFA verified successfully', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Verification successful',
            'redirect' => '/dashboard',
        ]);
    }

    /**
     * Enable SMS MFA for the current user.
     */
    public function enableSmsMfa(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
        ]);

        $user = $request->user();
        $phone = $request->phone;

        // Generate OTP for phone verification
        $otp = Otp::generate($phone, Otp::TYPE_PHONE_VERIFY);

        if (!$this->smsService->sendOtp($phone, $otp->code)) {
            return response()->json(['error' => 'Failed to send verification SMS'], 500);
        }

        // Store phone temporarily
        session(['pending_mfa_phone' => $phone]);

        return response()->json([
            'message' => 'Verification code sent to your phone',
        ]);
    }

    /**
     * Confirm SMS MFA setup.
     */
    public function confirmSmsMfa(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $phone = session('pending_mfa_phone');

        if (!$phone) {
            return response()->json(['error' => 'Please start MFA setup again'], 400);
        }

        if (!Otp::verify($phone, Otp::TYPE_PHONE_VERIFY, $request->code)) {
            return response()->json(['error' => 'Invalid verification code'], 401);
        }

        // Enable MFA
        $user->update([
            'phone' => $phone,
            'phone_verified_at' => now(),
            'mfa_enabled' => true,
            'mfa_method' => 'sms',
        ]);

        session()->forget('pending_mfa_phone');

        Log::info('SMS MFA enabled', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'SMS authentication enabled successfully',
        ]);
    }

    /**
     * Disable MFA for the current user.
     */
    public function disableMfa(Request $request)
    {
        $request->validate([
            'password' => 'required_if:has_password,true|string',
        ]);

        $user = $request->user();

        // Verify password if user has one
        if ($user->auth_provider === User::PROVIDER_EMAIL) {
            if (!Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
                return response()->json(['error' => 'Invalid password'], 401);
            }
        }

        $user->update([
            'mfa_enabled' => false,
            'mfa_method' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        Log::info('MFA disabled', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Multi-factor authentication disabled',
        ]);
    }
}
