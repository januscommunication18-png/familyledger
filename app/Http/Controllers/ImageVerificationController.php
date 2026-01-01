<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Notifications\OtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ImageVerificationController extends Controller
{
    /**
     * Session key for image verification status.
     */
    public const SESSION_KEY = 'image_verified';

    /**
     * Check if the current session is verified for image viewing.
     */
    public function status(Request $request)
    {
        return response()->json([
            'verified' => $request->session()->get(self::SESSION_KEY, false),
        ]);
    }

    /**
     * Send verification code via email or phone.
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'method' => 'required|in:email,phone',
        ]);

        $user = $request->user();
        $method = $request->method;

        // Rate limiting
        $key = 'image_verify_send:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Please wait {$seconds} seconds before requesting another code.",
            ], 429);
        }

        RateLimiter::hit($key, 60);

        try {
            if ($method === 'email') {
                // Generate OTP and send via email
                $otp = Otp::generate($user->email, Otp::TYPE_IMAGE_VERIFY);
                $user->notify(new OtpNotification($otp->code, 'image_verify'));

                $maskedEmail = $this->maskEmail($user->email);

                return response()->json([
                    'message' => "Verification code sent to {$maskedEmail}",
                    'method' => 'email',
                ]);
            } else {
                // Phone verification
                if (!$user->phone) {
                    return response()->json([
                        'error' => 'No phone number on file. Please use email verification.',
                    ], 400);
                }

                // Generate OTP
                $otp = Otp::generate($user->phone, Otp::TYPE_IMAGE_VERIFY);

                // Send via Twilio (if configured) or log in development
                $this->sendSmsCode($user, $otp->code);

                $maskedPhone = $this->maskPhone($user->phone);

                return response()->json([
                    'message' => "Verification code sent to {$maskedPhone}",
                    'method' => 'phone',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send image verification code', [
                'user_id' => $user->id,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to send verification code. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify the code and mark session as verified.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
            'method' => 'required|in:email,phone',
        ]);

        $user = $request->user();
        $method = $request->method;

        // Rate limiting for verification attempts
        $key = 'image_verify_attempt:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Too many attempts. Please wait {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 300);

        // Get the identifier based on method
        $identifier = $method === 'email' ? $user->email : $user->phone;

        // Verify the OTP
        if (!Otp::verify($identifier, Otp::TYPE_IMAGE_VERIFY, $request->code)) {
            return response()->json([
                'error' => 'Invalid or expired verification code.',
            ], 401);
        }

        // Mark session as verified
        $request->session()->put(self::SESSION_KEY, true);

        // Clear rate limits on success
        RateLimiter::clear($key);

        Log::info('Image verification successful', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Verification successful. You can now view and download images.',
            'verified' => true,
        ]);
    }

    /**
     * Send SMS code via Twilio or log in development.
     */
    protected function sendSmsCode($user, string $code): void
    {
        $twilioService = app(\App\Services\TwilioService::class);

        if ($twilioService->isConfigured()) {
            $phone = $user->country_code
                ? '+' . $user->country_code . $user->phone
                : '+1' . $user->phone;

            $twilioService->sendVerificationCode($phone, $code);
        } else {
            // Log in development
            Log::info('Image verification SMS code (dev mode)', [
                'user_id' => $user->id,
                'code' => $code,
            ]);
        }
    }

    /**
     * Mask email for display (jo***@example.com).
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '***@***.***';
        }

        $local = $parts[0];
        $domain = $parts[1];

        if (strlen($local) <= 2) {
            $maskedLocal = str_repeat('*', strlen($local));
        } else {
            $maskedLocal = substr($local, 0, 2) . str_repeat('*', strlen($local) - 2);
        }

        return $maskedLocal . '@' . $domain;
    }

    /**
     * Mask phone for display (***-***-1234).
     */
    protected function maskPhone(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) >= 4) {
            return '***-***-' . substr($clean, -4);
        }
        return '***-***-****';
    }
}
