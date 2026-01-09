<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

/**
 * API Controller for password reset functionality.
 */
class PasswordResetController extends Controller
{
    /**
     * Send password reset code to email.
     */
    public function sendResetCode(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower($request->email);

        // Rate limiting: 5 attempts per 5 minutes per email
        $key = 'api_password_reset:' . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->error(
                "Too many attempts. Please try again in {$seconds} seconds.",
                429
            );
        }

        RateLimiter::hit($key, 300);

        // Check if user exists
        $user = User::findByEmail($email);

        // Always return success to prevent email enumeration
        if (!$user) {
            Log::info('API: Password reset requested for non-existent email', ['email' => $email]);
            return $this->success([
                'expires_in' => Otp::EXPIRY_MINUTES * 60,
            ], 'If an account exists with this email, you will receive a reset code.');
        }

        // Generate OTP
        $otp = Otp::generate($email, Otp::TYPE_PASSWORD_RESET);

        // Send OTP via email
        Notification::route('mail', $email)
            ->notify(new OtpNotification($otp->code, 'password_reset'));

        Log::info('API: Password reset code sent', ['email' => $email]);

        return $this->success([
            'expires_in' => Otp::EXPIRY_MINUTES * 60,
        ], 'If an account exists with this email, you will receive a reset code.');
    }

    /**
     * Verify reset code and update password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = strtolower($request->email);
        $code = $request->code;

        // Rate limiting: 10 attempts per 5 minutes per email
        $key = 'api_password_reset_verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->error(
                "Too many attempts. Please try again in {$seconds} seconds.",
                429
            );
        }

        RateLimiter::hit($key, 300);

        // Verify OTP
        if (!Otp::verify($email, Otp::TYPE_PASSWORD_RESET, $code)) {
            Log::warning('API: Invalid password reset code attempt', ['email' => $email]);
            return $this->error('Invalid or expired reset code.', 401);
        }

        // Find user
        $user = User::findByEmail($email);

        if (!$user) {
            return $this->error('Unable to reset password.', 400);
        }

        // Update password
        $user->password = $request->password;
        $user->save();

        // Clear rate limits on success
        RateLimiter::clear($key);
        RateLimiter::clear('api_password_reset:' . $email);

        Log::info('API: Password reset successful', ['user_id' => $user->id]);

        return $this->success(null, 'Password reset successful. You can now log in.');
    }

    /**
     * Resend reset code.
     */
    public function resendCode(Request $request): JsonResponse
    {
        return $this->sendResetCode($request);
    }
}
