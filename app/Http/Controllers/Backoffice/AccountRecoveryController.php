<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AccountRecoveryController extends Controller
{
    /**
     * Show account recovery index/search page.
     */
    public function index(): View
    {
        return view('backoffice.account-recovery.index');
    }

    /**
     * Search for a client by email or tenant ID.
     */
    public function search(Request $request): RedirectResponse
    {
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $query = trim($request->query('query'));

        // Try to find by email
        $user = User::findByEmail($query);

        if ($user) {
            return redirect()->route('backoffice.account-recovery.show', $user->tenant_id);
        }

        // Try by tenant ID
        $tenant = Tenant::find($query);
        if ($tenant) {
            return redirect()->route('backoffice.account-recovery.show', $tenant->id);
        }

        return back()
            ->withInput()
            ->withErrors(['query' => 'No client found with that email or ID.']);
    }

    /**
     * Show client recovery page.
     */
    public function show(Tenant $client): View
    {
        $admin = Auth::guard('backoffice')->user();
        $admin->logActivity(ActivityLog::ACTION_VIEW_RECOVERY_PAGE, $client->id);

        // Get owner user (first user who registered)
        $owner = User::where('tenant_id', $client->id)
            ->orderBy('created_at')
            ->first();

        // Check if recovery code is verified in session
        $isVerified = session('recovery_verified_' . $client->id, false);
        $verifiedAt = session('recovery_verified_at_' . $client->id);

        // Check if verification has expired (30 minutes)
        if ($isVerified && $verifiedAt && now()->diffInMinutes($verifiedAt) > 30) {
            session()->forget('recovery_verified_' . $client->id);
            session()->forget('recovery_verified_at_' . $client->id);
            $isVerified = false;
        }

        return view('backoffice.account-recovery.show', compact('client', 'owner', 'isVerified'));
    }

    /**
     * Verify recovery code provided by client.
     */
    public function verifyCode(Request $request, Tenant $client): JsonResponse
    {
        $request->validate([
            'recovery_code' => 'required|string|size:16',
        ]);

        $admin = Auth::guard('backoffice')->user();

        $owner = User::where('tenant_id', $client->id)
            ->orderBy('created_at')
            ->first();

        if (!$owner || !$owner->hasAccountRecoveryCode()) {
            $admin->logActivity(
                ActivityLog::ACTION_RECOVERY_VERIFICATION_FAILED,
                $client->id,
                'No recovery code set for this account'
            );
            return response()->json([
                'success' => false,
                'message' => 'This account does not have a recovery code set.',
            ], 422);
        }

        if (!$owner->verifyAccountRecoveryCode($request->recovery_code)) {
            $admin->logActivity(
                ActivityLog::ACTION_RECOVERY_VERIFICATION_FAILED,
                $client->id,
                'Invalid recovery code provided'
            );
            return response()->json([
                'success' => false,
                'message' => 'Invalid recovery code.',
            ], 422);
        }

        // Grant session access for recovery actions
        session(['recovery_verified_' . $client->id => true]);
        session(['recovery_verified_at_' . $client->id => now()]);

        $admin->logActivity(
            ActivityLog::ACTION_RECOVERY_VERIFICATION_SUCCESS,
            $client->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Recovery code verified. You can now perform recovery actions.',
        ]);
    }

    /**
     * Change client's email address.
     */
    public function changeEmail(Request $request, Tenant $client): RedirectResponse
    {
        $this->ensureRecoveryVerified($client);

        $request->validate([
            'new_email' => 'required|email',
            'reason' => 'required|string|max:500',
        ]);

        $admin = Auth::guard('backoffice')->user();
        $owner = User::where('tenant_id', $client->id)->orderBy('created_at')->first();

        // Check if email is already used
        $existingUser = User::findByEmail($request->new_email);
        if ($existingUser && $existingUser->id !== $owner->id) {
            return back()->withErrors(['new_email' => 'This email is already in use by another account.']);
        }

        $oldEmail = $owner->email;
        $owner->email = $request->new_email;
        $owner->email_verified_at = null; // Require re-verification
        $owner->save();

        $admin->logActivity(
            ActivityLog::ACTION_RECOVERY_CHANGE_EMAIL,
            $client->id,
            "Email changed. Reason: {$request->reason}"
        );

        return back()->with('message', 'Email address updated successfully. User will need to verify their new email.');
    }

    /**
     * Reset client's password.
     */
    public function resetPassword(Request $request, Tenant $client): RedirectResponse
    {
        $this->ensureRecoveryVerified($client);

        $request->validate([
            'new_password' => 'required|string|min:8',
            'reason' => 'required|string|max:500',
        ]);

        $admin = Auth::guard('backoffice')->user();
        $owner = User::where('tenant_id', $client->id)->orderBy('created_at')->first();

        $owner->password = Hash::make($request->new_password);
        $owner->save();

        $admin->logActivity(
            ActivityLog::ACTION_RECOVERY_RESET_PASSWORD,
            $client->id,
            "Password reset. Reason: {$request->reason}"
        );

        return back()->with('message', 'Password has been reset successfully.');
    }

    /**
     * Disable 2FA for client.
     */
    public function disable2fa(Request $request, Tenant $client): RedirectResponse
    {
        $this->ensureRecoveryVerified($client);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $admin = Auth::guard('backoffice')->user();
        $owner = User::where('tenant_id', $client->id)->orderBy('created_at')->first();

        $owner->update([
            'mfa_enabled' => false,
            'mfa_method' => null,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'phone_2fa_enabled' => false,
            'recovery_codes' => null,
        ]);

        $admin->logActivity(
            ActivityLog::ACTION_RECOVERY_DISABLE_2FA,
            $client->id,
            "2FA disabled. Reason: {$request->reason}"
        );

        return back()->with('message', 'Two-factor authentication has been disabled.');
    }

    /**
     * Reset phone number for client.
     */
    public function resetPhone(Request $request, Tenant $client): RedirectResponse
    {
        $this->ensureRecoveryVerified($client);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $admin = Auth::guard('backoffice')->user();
        $owner = User::where('tenant_id', $client->id)->orderBy('created_at')->first();

        // If SMS 2FA was enabled, also disable it
        if ($owner->mfa_method === 'sms') {
            $owner->update([
                'mfa_enabled' => false,
                'mfa_method' => null,
                'phone_2fa_enabled' => false,
            ]);
        }

        $owner->update([
            'phone' => null,
        ]);

        $admin->logActivity(
            ActivityLog::ACTION_RECOVERY_RESET_PHONE,
            $client->id,
            "Phone number reset. Reason: {$request->reason}"
        );

        return back()->with('message', 'Phone number has been reset.');
    }

    /**
     * Revoke recovery access (clear session).
     */
    public function revokeAccess(Tenant $client): JsonResponse
    {
        session()->forget('recovery_verified_' . $client->id);
        session()->forget('recovery_verified_at_' . $client->id);

        return response()->json(['success' => true]);
    }

    /**
     * Ensure recovery code has been verified before allowing actions.
     */
    protected function ensureRecoveryVerified(Tenant $client): void
    {
        if (!session('recovery_verified_' . $client->id)) {
            abort(403, 'Recovery code verification required.');
        }

        // Check if verification hasn't expired (30 minutes)
        $verifiedAt = session('recovery_verified_at_' . $client->id);
        if ($verifiedAt && now()->diffInMinutes($verifiedAt) > 30) {
            session()->forget('recovery_verified_' . $client->id);
            session()->forget('recovery_verified_at_' . $client->id);
            abort(403, 'Recovery session expired. Please verify the recovery code again.');
        }
    }
}
