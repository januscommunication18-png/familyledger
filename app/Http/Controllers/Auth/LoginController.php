<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Controller for traditional email/password login.
 */
class LoginController extends Controller
{
    /**
     * Show login form.
     */
    public function show()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'remember' => 'boolean',
        ]);

        $email = strtolower($request->email);

        // Rate limiting
        $key = 'login:' . $email . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            $this->logLoginAttempt($email, $request, false, 'rate_limited');

            return response()->json([
                'error' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 300);

        // Attempt authentication
        if (!Auth::attempt(['email' => $email, 'password' => $request->password], $request->remember)) {
            $this->logLoginAttempt($email, $request, false, 'invalid_credentials');

            return response()->json([
                'error' => 'Invalid email or password.',
            ], 401);
        }

        $user = Auth::user();

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            $this->logLoginAttempt($email, $request, false, 'account_inactive');

            return response()->json([
                'error' => 'Your account has been deactivated.',
            ], 403);
        }

        // Clear rate limits on success
        RateLimiter::clear($key);

        // Check if MFA is required
        if ($user->hasTwoFactorEnabled() || $user->hasSmsMfaEnabled()) {
            Auth::logout();
            session(['mfa_required' => true, 'mfa_user_id' => $user->id]);

            $this->logLoginAttempt($email, $request, true, 'mfa_required', $user->id);

            return response()->json([
                'mfa_required' => true,
                'redirect' => '/auth/mfa',
            ]);
        }

        $user->recordLogin();
        $this->logLoginAttempt($email, $request, true, null, $user->id);

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'redirect' => '/dashboard',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Logged out successfully',
                'redirect' => '/login',
            ]);
        }

        return redirect('/login');
    }

    /**
     * Log login attempt for security auditing.
     */
    protected function logLoginAttempt(
        string $email,
        Request $request,
        bool $successful,
        ?string $failureReason = null,
        ?int $userId = null
    ): void {
        Log::channel('security')->info('Login attempt', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $successful,
            'failure_reason' => $failureReason,
            'user_id' => $userId,
        ]);
    }
}
