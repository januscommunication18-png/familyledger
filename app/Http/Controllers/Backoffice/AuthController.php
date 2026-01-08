<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Mail\BackofficeSecurityCode;
use App\Models\Backoffice\Admin;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    /**
     * Show login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('backoffice')->check()) {
            return redirect()->route('backoffice.dashboard');
        }

        return view('backoffice.auth.login');
    }

    /**
     * Handle login attempt - Step 1: Validate credentials and send security code.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
        }

        if (!$admin->is_active) {
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        // Generate and send security code
        $code = $admin->generateSecurityCode();

        // Store admin ID in session for next step
        session(['backoffice_pending_admin_id' => $admin->id]);

        // Send security code email
        Mail::to($admin->email)->send(new BackofficeSecurityCode($code, $admin->name));

        return redirect()->route('backoffice.verify-code')
            ->with('message', 'A security code has been sent to your email.');
    }

    /**
     * Show security code verification form.
     */
    public function showVerifyCode(): View|RedirectResponse
    {
        if (!session('backoffice_pending_admin_id')) {
            return redirect()->route('backoffice.login');
        }

        return view('backoffice.auth.verify-code');
    }

    /**
     * Verify security code and complete login.
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $adminId = session('backoffice_pending_admin_id');

        if (!$adminId) {
            return redirect()->route('backoffice.login')
                ->withErrors(['code' => 'Session expired. Please login again.']);
        }

        $admin = Admin::find($adminId);

        if (!$admin || !$admin->verifySecurityCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired security code.']);
        }

        // Clear security code
        $admin->clearSecurityCode();

        // Update last login info
        $admin->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Log activity
        $admin->logActivity(ActivityLog::ACTION_LOGIN);

        // Login the admin
        Auth::guard('backoffice')->login($admin, $request->boolean('remember'));

        // Clear session
        session()->forget('backoffice_pending_admin_id');

        return redirect()->intended(route('backoffice.dashboard'));
    }

    /**
     * Resend security code.
     */
    public function resendCode(): RedirectResponse
    {
        $adminId = session('backoffice_pending_admin_id');

        if (!$adminId) {
            return redirect()->route('backoffice.login');
        }

        $admin = Admin::find($adminId);

        if (!$admin) {
            return redirect()->route('backoffice.login');
        }

        $code = $admin->generateSecurityCode();

        // Send security code email
        Mail::to($admin->email)->send(new BackofficeSecurityCode($code, $admin->name));

        return back()
            ->with('message', 'A new security code has been sent to your email.');
    }

    /**
     * Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $admin = Auth::guard('backoffice')->user();

        if ($admin) {
            $admin->logActivity(ActivityLog::ACTION_LOGOUT);
        }

        Auth::guard('backoffice')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backoffice.login');
    }

    /**
     * Show forgot password form.
     */
    public function showForgotPassword(): View
    {
        return view('backoffice.auth.forgot-password');
    }

    /**
     * Send password reset link.
     */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::broker('backoffice_admins')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('message', 'Password reset link has been sent to your email.')
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show reset password form.
     */
    public function showResetPassword(Request $request, string $token): View
    {
        return view('backoffice.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('backoffice_admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Admin $admin, string $password) {
                $admin->update([
                    'password' => Hash::make($password),
                ]);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('backoffice.login')->with('message', 'Password has been reset successfully.')
            : back()->withErrors(['email' => __($status)]);
    }
}
