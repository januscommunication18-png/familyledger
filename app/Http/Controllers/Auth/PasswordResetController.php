<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Notifications\OtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Controller for password reset functionality.
 */
class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Send password reset code to email.
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = strtolower($request->email);

        // Rate limiting: 5 attempts per 5 minutes per email
        $key = 'password_reset:' . $email;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Too many attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 300);

        // Check if user exists
        $user = User::findByEmail($email);

        // Always return success to prevent email enumeration
        if (!$user) {
            Log::info('Password reset requested for non-existent email', ['email' => $email]);
            return response()->json([
                'message' => 'If an account exists with this email, you will receive a reset code.',
            ]);
        }

        // Generate OTP
        $otp = Otp::generate($email, Otp::TYPE_PASSWORD_RESET);

        // Send OTP via email
        Notification::route('mail', $email)
            ->notify(new OtpNotification($otp->code, 'password_reset'));

        Log::info('Password reset code sent', ['email' => $email]);

        return response()->json([
            'message' => 'If an account exists with this email, you will receive a reset code.',
            'expires_in' => Otp::EXPIRY_MINUTES * 60,
        ]);
    }

    /**
     * Verify reset code and update password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = strtolower($request->email);
        $code = $request->code;

        // Rate limiting: 10 attempts per 5 minutes per email
        $key = 'password_reset_verify:' . $email;
        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'error' => "Too many attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 300);

        // Verify OTP
        if (!Otp::verify($email, Otp::TYPE_PASSWORD_RESET, $code)) {
            Log::warning('Invalid password reset code attempt', ['email' => $email]);

            return response()->json([
                'error' => 'Invalid or expired reset code.',
            ], 401);
        }

        // Find user
        $user = User::findByEmail($email);

        if (!$user) {
            return response()->json([
                'error' => 'Unable to reset password.',
            ], 400);
        }

        // Update password
        $user->password = $request->password;
        $user->save();

        // Clear rate limits on success
        RateLimiter::clear($key);
        RateLimiter::clear('password_reset:' . $email);

        Log::info('Password reset successful', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Password reset successful. You can now log in.',
            'redirect' => '/login',
        ]);
    }

    /**
     * Resend reset code.
     */
    public function resendCode(Request $request)
    {
        return $this->sendResetCode($request);
    }
}
