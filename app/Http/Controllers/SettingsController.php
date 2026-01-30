<?php

namespace App\Http\Controllers;

use App\Mail\RecoveryCodeSetMail;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'profile');

        // Get active sessions for security tab
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($request) {
                $session->is_current = $session->id === $request->session()->getId();
                $session->last_activity_human = \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans();
                $session->device = $this->parseUserAgent($session->user_agent);
                return $session;
            });

        // Get connected social accounts
        $socialAccounts = SocialAccount::where('user_id', $user->id)->get();

        // Get recent login attempts
        $loginAttempts = DB::table('login_attempts')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Get tenant settings for family tab
        $tenant = $user->tenant;

        // Check if user is family owner
        $owner = \App\Models\User::where('tenant_id', $tenant->id)->orderBy('created_at')->first();
        $isOwner = $owner && $owner->id === $user->id;

        return view('pages.settings.index', compact(
            'user',
            'tab',
            'sessions',
            'socialAccounts',
            'loginAttempts',
            'tenant',
            'isOwner'
        ));
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? null;

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('do_spaces')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('family-ledger/avatars', 'do_spaces');
            $user->avatar = $path;
        }

        $user->save();

        // Sync name to all linked family members
        if ($user->id) {
            $nameParts = explode(' ', $user->name, 2);
            \App\Models\FamilyMember::where('linked_user_id', $user->id)
                ->update([
                    'first_name' => $nameParts[0],
                    'last_name' => $nameParts[1] ?? '',
                    'phone' => $user->phone,
                ]);
        }

        return redirect()->route('settings.index', ['tab' => 'profile'])
            ->with('success', 'Profile updated successfully');
    }

    /**
     * Remove user avatar.
     */
    public function removeAvatar(Request $request)
    {
        $user = Auth::user();

        if ($user->avatar) {
            // Delete the avatar from storage
            Storage::disk('do_spaces')->delete($user->avatar);
            $user->avatar = null;
            $user->save();

            return redirect()->route('settings.index', ['tab' => 'profile'])
                ->with('success', 'Profile photo removed successfully');
        }

        return redirect()->route('settings.index', ['tab' => 'profile'])
            ->with('error', 'No profile photo to remove');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('settings.index', ['tab' => 'security'])
            ->with('success', 'Password updated successfully');
    }

    /**
     * Revoke a session.
     */
    public function revokeSession(Request $request, string $sessionId)
    {
        $user = Auth::user();

        // Prevent revoking current session
        if ($sessionId === $request->session()->getId()) {
            return back()->with('error', 'You cannot revoke your current session.');
        }

        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', $user->id)
            ->delete();

        return redirect()->route('settings.index', ['tab' => 'security'])
            ->with('success', 'Session revoked successfully');
    }

    /**
     * Revoke all other sessions.
     */
    public function revokeAllSessions(Request $request)
    {
        $user = Auth::user();
        $currentSessionId = $request->session()->getId();

        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return redirect()->route('settings.index', ['tab' => 'security'])
            ->with('success', 'All other sessions have been revoked');
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $preferences = [
            'email_notifications' => $request->boolean('email_notifications'),
            'sms_notifications' => $request->boolean('sms_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
            'notify_expense_alerts' => $request->boolean('notify_expense_alerts'),
            'notify_task_reminders' => $request->boolean('notify_task_reminders'),
            'notify_coparent_messages' => $request->boolean('notify_coparent_messages'),
            'notify_calendar_events' => $request->boolean('notify_calendar_events'),
            'notify_document_expiry' => $request->boolean('notify_document_expiry'),
            'weekly_digest' => $request->boolean('weekly_digest'),
            'marketing_emails' => $request->boolean('marketing_emails'),
        ];

        foreach ($preferences as $key => $value) {
            $tenant->setSetting('notifications.' . $key, $value);
        }

        return redirect()->route('settings.index', ['tab' => 'notifications'])
            ->with('success', 'Notification preferences updated');
    }

    /**
     * Update appearance settings.
     */
    public function updateAppearance(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $validated = $request->validate([
            'theme' => 'required|in:light,dark,system',
            'timezone' => 'required|timezone',
            'date_format' => 'required|in:M d, Y,d/m/Y,Y-m-d,m/d/Y',
            'currency' => 'required|in:USD,EUR,GBP,CAD,AUD',
        ]);

        $tenant->setSetting('appearance.theme', $validated['theme']);
        $tenant->setSetting('appearance.timezone', $validated['timezone']);
        $tenant->setSetting('appearance.date_format', $validated['date_format']);
        $tenant->setSetting('appearance.currency', $validated['currency']);

        return redirect()->route('settings.index', ['tab' => 'appearance'])
            ->with('success', 'Appearance settings updated');
    }

    /**
     * Update privacy settings.
     */
    public function updatePrivacy(Request $request)
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $settings = [
            'profile_visibility' => $request->input('profile_visibility', 'family'),
            'activity_tracking' => $request->boolean('activity_tracking'),
            'share_analytics' => $request->boolean('share_analytics'),
        ];

        foreach ($settings as $key => $value) {
            $tenant->setSetting('privacy.' . $key, $value);
        }

        return redirect()->route('settings.index', ['tab' => 'privacy'])
            ->with('success', 'Privacy settings updated');
    }

    /**
     * Export user data.
     */
    public function exportData()
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        // Collect all user data
        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'created_at' => $user->created_at,
            ],
            'tenant' => [
                'name' => $tenant->name,
                'created_at' => $tenant->created_at,
            ],
            'family_members' => \App\Models\FamilyMember::where('tenant_id', $tenant->id)->get()->toArray(),
            'transactions' => \App\Models\BudgetTransaction::where('tenant_id', $tenant->id)->get()->toArray(),
            'journal_entries' => \App\Models\JournalEntry::where('tenant_id', $tenant->id)->get()->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        $filename = 'familyledger-export-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Request account deletion.
     */
    public function requestAccountDeletion(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The password is incorrect.');
                }
            }],
            'confirmation' => 'required|in:DELETE',
        ]);

        // For now, just mark the account for deletion
        // In production, you might want to schedule actual deletion
        $user->tenant->setSetting('deletion_requested_at', now()->toIso8601String());
        $user->tenant->setSetting('deletion_requested_by', $user->id);

        // Log out the user
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Your account deletion has been requested. Your data will be permanently deleted within 30 days.');
    }

    /**
     * Generate a new account recovery code.
     */
    public function generateRecoveryCode(Request $request)
    {
        $code = User::generateRecoveryCode();

        return response()->json([
            'success' => true,
            'code' => $code,
        ]);
    }

    /**
     * Save the account recovery code (auto-generated or user-provided).
     */
    public function saveRecoveryCode(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'recovery_code' => ['required', 'string', 'size:16', 'regex:/^[0-9]+$/'],
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
        ]);

        $isUpdate = $user->hasAccountRecoveryCode();

        $user->setAccountRecoveryCode($request->recovery_code);

        // Send notification email with the recovery code for safekeeping
        Mail::to($user->email)->send(new RecoveryCodeSetMail(
            $user->name,
            $request->recovery_code,
            $isUpdate ? 'updated' : 'set'
        ));

        return redirect()->route('settings.index', ['tab' => 'security'])
            ->with('success', 'Account recovery code has been ' . ($isUpdate ? 'updated' : 'set') . ' successfully. Please store it in a safe place.');
    }

    /**
     * Parse user agent to get device info.
     */
    private function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return ['browser' => 'Unknown', 'platform' => 'Unknown', 'icon' => 'device-desktop'];
        }

        $browser = 'Unknown';
        $platform = 'Unknown';
        $icon = 'device-desktop';

        // Detect browser
        if (str_contains($userAgent, 'Chrome')) {
            $browser = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $browser = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            $browser = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $browser = 'Edge';
        }

        // Detect platform
        if (str_contains($userAgent, 'Windows')) {
            $platform = 'Windows';
            $icon = 'device-desktop';
        } elseif (str_contains($userAgent, 'Macintosh')) {
            $platform = 'Mac';
            $icon = 'device-laptop';
        } elseif (str_contains($userAgent, 'iPhone')) {
            $platform = 'iPhone';
            $icon = 'device-mobile';
        } elseif (str_contains($userAgent, 'Android')) {
            $platform = 'Android';
            $icon = 'device-mobile';
        } elseif (str_contains($userAgent, 'Linux')) {
            $platform = 'Linux';
            $icon = 'device-desktop';
        }

        return ['browser' => $browser, 'platform' => $platform, 'icon' => $icon];
    }
}
