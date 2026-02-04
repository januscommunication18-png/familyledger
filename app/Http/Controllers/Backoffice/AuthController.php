<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Mail\BackofficeAccessCode;
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
     * Show request access form (enter email only).
     */
    public function showRequestAccess(): View|RedirectResponse
    {
        // If already has access, redirect to login
        if (session('backoffice_access_granted')) {
            return redirect()->route('backoffice.login');
        }

        // If already logged in, redirect to dashboard
        if (Auth::guard('backoffice')->check()) {
            return redirect()->route('backoffice.dashboard');
        }

        return view('backoffice.auth.request-access');
    }

    /**
     * Handle request access - send access code to email.
     */
    public function requestAccess(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin) {
            return back()->withErrors(['email' => 'This email is not authorized for backoffice access.'])->withInput();
        }

        if (!$admin->is_active) {
            return back()->withErrors(['email' => 'Your account has been deactivated.'])->withInput();
        }

        // Generate and send access code
        $code = $admin->generateAccessCode();

        // Store admin ID in session
        session(['backoffice_pending_access_admin_id' => $admin->id]);

        // Send access code email
        Mail::to($admin->email)->send(new BackofficeAccessCode($code, $admin->name));

        // In local environment, flash code to session for display
        $redirect = redirect()->route('backoffice.verify-access')
            ->with('message', 'An access code has been sent to your email.');

        if (app()->environment('local')) {
            $redirect->with('dev_code', $code);
        }

        return $redirect;
    }

    /**
     * Show verify access form.
     */
    public function showVerifyAccess(): View|RedirectResponse
    {
        if (!session('backoffice_pending_access_admin_id')) {
            return redirect()->route('backoffice.request-access');
        }

        return view('backoffice.auth.verify-access');
    }

    /**
     * Verify access code.
     */
    public function verifyAccess(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $adminId = session('backoffice_pending_access_admin_id');

        if (!$adminId) {
            return redirect()->route('backoffice.request-access')
                ->withErrors(['code' => 'Session expired. Please start again.']);
        }

        $admin = Admin::find($adminId);

        if (!$admin || !$admin->verifyAccessCode($request->code)) {
            return back()->withErrors(['code' => 'Invalid or expired access code.']);
        }

        // Clear access code
        $admin->clearAccessCode();

        // Grant access and clear pending
        session()->forget('backoffice_pending_access_admin_id');
        session(['backoffice_access_granted' => true]);
        session(['backoffice_access_email' => $admin->email]);

        return redirect()->route('backoffice.login')
            ->with('message', 'Access verified. Please enter your password to login.');
    }

    /**
     * Resend access code.
     */
    public function resendAccessCode(): RedirectResponse
    {
        $adminId = session('backoffice_pending_access_admin_id');

        if (!$adminId) {
            return redirect()->route('backoffice.request-access');
        }

        $admin = Admin::find($adminId);

        if (!$admin) {
            return redirect()->route('backoffice.request-access');
        }

        $code = $admin->generateAccessCode();

        // Send access code email
        Mail::to($admin->email)->send(new BackofficeAccessCode($code, $admin->name));

        $redirect = back()->with('message', 'A new access code has been sent to your email.');

        if (app()->environment('local')) {
            $redirect->with('dev_code', $code);
        }

        return $redirect;
    }

    /**
     * Show login form (only after access verification).
     */
    public function showLogin(): View|RedirectResponse
    {
        // If not access granted, redirect to request access
        if (!session('backoffice_access_granted')) {
            return redirect()->route('backoffice.request-access');
        }

        // If already logged in, redirect to dashboard
        if (Auth::guard('backoffice')->check()) {
            return redirect()->route('backoffice.dashboard');
        }

        $email = session('backoffice_access_email');

        return view('backoffice.auth.login', compact('email'));
    }

    /**
     * Handle login attempt - Step 1: Validate credentials and send security code.
     */
    public function login(Request $request): RedirectResponse
    {
        // Ensure access was granted
        if (!session('backoffice_access_granted')) {
            return redirect()->route('backoffice.request-access');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->withErrors(['password' => 'Invalid password.'])->withInput();
        }

        if (!$admin->is_active) {
            return back()->withErrors(['password' => 'Your account has been deactivated.'])->withInput();
        }

        // Generate and send security code
        $code = $admin->generateSecurityCode();

        // Store admin ID in session for next step
        session(['backoffice_pending_admin_id' => $admin->id]);

        // Send security code email
        Mail::to($admin->email)->send(new BackofficeSecurityCode($code, $admin->name));

        $redirect = redirect()->route('backoffice.verify-code')
            ->with('message', 'A security code has been sent to your email.');

        if (app()->environment('local')) {
            $redirect->with('dev_code', $code);
        }

        return $redirect;
    }

    /**
     * Show security code verification form.
     */
    public function showVerifyCode(): View|RedirectResponse
    {
        if (!session('backoffice_pending_admin_id')) {
            return redirect()->route('backoffice.request-access');
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
            return redirect()->route('backoffice.request-access')
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

        // Clear all session data
        session()->forget([
            'backoffice_pending_admin_id',
            'backoffice_access_granted',
            'backoffice_access_email',
        ]);

        return redirect()->intended(route('backoffice.dashboard'));
    }

    /**
     * Resend security code.
     */
    public function resendCode(): RedirectResponse
    {
        $adminId = session('backoffice_pending_admin_id');

        if (!$adminId) {
            return redirect()->route('backoffice.request-access');
        }

        $admin = Admin::find($adminId);

        if (!$admin) {
            return redirect()->route('backoffice.request-access');
        }

        $code = $admin->generateSecurityCode();

        // Send security code email
        Mail::to($admin->email)->send(new BackofficeSecurityCode($code, $admin->name));

        $redirect = back()->with('message', 'A new security code has been sent to your email.');

        if (app()->environment('local')) {
            $redirect->with('dev_code', $code);
        }

        return $redirect;
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

        return redirect()->route('backoffice.request-access');
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
            ? redirect()->route('backoffice.request-access')->with('message', 'Password has been reset successfully.')
            : back()->withErrors(['email' => __($status)]);
    }
}
