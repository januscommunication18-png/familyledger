<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;

/**
 * Controller for Multi-Factor Authentication (MFA).
 * Supports Email, Authenticator App, and SMS authentication.
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

        // Determine available MFA methods for this user
        $availableMethods = [];

        // Check if authenticator is set up
        if ($user->two_factor_secret && $user->two_factor_confirmed_at) {
            $availableMethods[] = 'authenticator';
        }

        // Check if email is verified
        if ($user->email && $user->email_verified_at) {
            $availableMethods[] = 'email';
        }

        // SMS is always shown but marked as coming soon if not configured
        $smsAvailable = $user->phone && $user->phone_verified_at && $this->smsService->isConfigured();
        if ($smsAvailable) {
            $availableMethods[] = 'sms';
        }

        // Default method priority: authenticator > email > sms
        $defaultMethod = null;
        if (in_array('authenticator', $availableMethods)) {
            $defaultMethod = 'authenticator';
        } elseif (in_array('email', $availableMethods)) {
            $defaultMethod = 'email';
        } elseif (in_array('sms', $availableMethods)) {
            $defaultMethod = 'sms';
        }

        // If user has a preferred method and it's available, use it
        if ($user->mfa_method && in_array($user->mfa_method, $availableMethods)) {
            $defaultMethod = $user->mfa_method;
        }

        return view('auth.mfa', [
            'method' => $defaultMethod,
            'availableMethods' => $availableMethods,
            'email' => $user->email ? $this->maskEmail($user->email) : null,
            'phone_last_four' => $user->phone ? substr($user->phone, -4) : null,
            'smsConfigured' => $this->smsService->isConfigured(),
            'hasPhone' => (bool) $user->phone,
        ]);
    }

    /**
     * Mask email for display.
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        if (strlen($name) <= 2) {
            $masked = $name[0] . '***';
        } else {
            $masked = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 3));
        }

        return $masked . '@' . $domain;
    }

    /**
     * Send Email MFA code.
     */
    public function sendEmailCode(Request $request)
    {
        $userId = session('mfa_user_id');
        $user = User::find($userId);

        if (!$user || !$user->email) {
            return response()->json(['error' => 'Email not configured'], 400);
        }

        // Rate limiting
        $key = 'mfa_email:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Too many attempts. Try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 120);

        // Generate OTP
        $otp = Otp::generate($user->email, Otp::TYPE_EMAIL_MFA);

        // Send email
        try {
            Mail::send('emails.mfa-code', ['code' => $otp->code, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Your Family Ledger Login Code');
            });

            Log::info('MFA email sent', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Verification code sent to your email',
                'email' => $this->maskEmail($user->email),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send MFA email', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to send email'], 500);
        }
    }

    /**
     * Send SMS MFA code.
     */
    public function sendSmsCode(Request $request)
    {
        $userId = session('mfa_user_id');
        $user = User::find($userId);

        if (!$user || !$user->phone) {
            return response()->json(['error' => 'Phone number not configured'], 400);
        }

        if (!$this->smsService->isConfigured()) {
            return response()->json(['error' => 'SMS service not available'], 503);
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
            'code' => 'required|string|min:6|max:6',
            'method' => 'nullable|string|in:authenticator,email,sms',
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

        // Determine which method to use
        $method = $request->method ?? $user->mfa_method ?? 'authenticator';
        $verified = false;

        if ($method === 'authenticator') {
            // Verify using Google2FA
            if ($user->two_factor_secret) {
                try {
                    $google2fa = new Google2FA();
                    $secret = decrypt($user->two_factor_secret);
                    $verified = $google2fa->verifyKey($secret, $request->code);
                } catch (\Exception $e) {
                    Log::error('Authenticator verification failed', ['error' => $e->getMessage()]);
                }
            }
        } elseif ($method === 'email') {
            // Verify using Email OTP
            if ($user->email) {
                $verified = Otp::verify($user->email, Otp::TYPE_EMAIL_MFA, $request->code);
            }
        } elseif ($method === 'sms') {
            // Verify using SMS OTP
            if ($user->phone) {
                $verified = Otp::verify($user->phone, Otp::TYPE_SMS_MFA, $request->code);
            }
        }

        if (!$verified) {
            Log::warning('Invalid MFA code', ['user_id' => $user->id, 'method' => $method]);
            return response()->json(['error' => 'Invalid verification code'], 401);
        }

        // Clear session and rate limits
        RateLimiter::clear($key);
        session()->forget(['mfa_required', 'mfa_user_id']);

        // Login user
        Auth::login($user, true);
        $user->recordLogin();

        Log::info('MFA verified successfully', ['user_id' => $user->id, 'method' => $method]);

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

        if (!$this->smsService->isConfigured()) {
            return response()->json(['error' => 'SMS service not available'], 503);
        }

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
