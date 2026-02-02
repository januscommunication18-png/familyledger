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

            $path = $request->file('avatar')->storePublicly('family-ledger/avatars', 'do_spaces');
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

    // =====================================================
    // API METHODS (JSON responses for mobile apps)
    // =====================================================

    /**
     * Get all settings (API).
     */
    public function getSettingsApi(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar ? Storage::disk('do_spaces')->url($user->avatar) : null,
                    'mfa_enabled' => $user->mfa_enabled ?? false,
                    'mfa_method' => $user->mfa_method,
                    'has_recovery_code' => $user->hasAccountRecoveryCode(),
                    'created_at' => $user->created_at?->toIso8601String(),
                ],
                'notifications' => [
                    'email_notifications' => $tenant->getSetting('notifications.email_notifications', true),
                    'sms_notifications' => $tenant->getSetting('notifications.sms_notifications', false),
                    'push_notifications' => $tenant->getSetting('notifications.push_notifications', true),
                    'notify_expense_alerts' => $tenant->getSetting('notifications.notify_expense_alerts', true),
                    'notify_task_reminders' => $tenant->getSetting('notifications.notify_task_reminders', true),
                    'notify_coparent_messages' => $tenant->getSetting('notifications.notify_coparent_messages', true),
                    'notify_calendar_events' => $tenant->getSetting('notifications.notify_calendar_events', true),
                    'notify_document_expiry' => $tenant->getSetting('notifications.notify_document_expiry', true),
                    'weekly_digest' => $tenant->getSetting('notifications.weekly_digest', false),
                    'marketing_emails' => $tenant->getSetting('notifications.marketing_emails', false),
                ],
                'appearance' => [
                    'theme' => $tenant->getSetting('appearance.theme', 'system'),
                    'timezone' => $tenant->getSetting('appearance.timezone', 'America/New_York'),
                    'date_format' => $tenant->getSetting('appearance.date_format', 'M d, Y'),
                    'currency' => $tenant->getSetting('appearance.currency', 'USD'),
                ],
                'privacy' => [
                    'profile_visibility' => $tenant->getSetting('privacy.profile_visibility', 'family'),
                    'activity_tracking' => $tenant->getSetting('privacy.activity_tracking', true),
                    'share_analytics' => $tenant->getSetting('privacy.share_analytics', false),
                ],
            ],
        ]);
    }

    /**
     * Update profile (API).
     */
    public function updateProfileApi(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? null;

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('do_spaces')->delete($user->avatar);
            }
            $path = $request->file('avatar')->storePublicly('family-ledger/avatars', 'do_spaces');
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

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar ? Storage::disk('do_spaces')->url($user->avatar) : null,
                ],
            ],
        ]);
    }

    /**
     * Remove avatar (API).
     */
    public function removeAvatarApi(Request $request)
    {
        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('do_spaces')->delete($user->avatar);
            $user->avatar = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile photo removed successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No profile photo to remove',
        ], 400);
    }

    /**
     * Update password (API).
     */
    public function updatePasswordApi(Request $request)
    {
        $user = $request->user();

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

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Get sessions (API).
     */
    public function getSessionsApi(Request $request)
    {
        $user = $request->user();

        // For API tokens, we track via personal_access_tokens table
        $tokens = DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', User::class)
            ->orderByDesc('last_used_at')
            ->get()
            ->map(function ($token) use ($request) {
                $isCurrent = $request->bearerToken() &&
                    hash('sha256', explode('|', $request->bearerToken())[1] ?? '') === $token->token;
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'device' => $this->parseUserAgent($token->name),
                    'last_used_at' => $token->last_used_at,
                    'last_used_human' => $token->last_used_at ? \Carbon\Carbon::parse($token->last_used_at)->diffForHumans() : 'Never',
                    'created_at' => $token->created_at,
                    'is_current' => $isCurrent,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Sessions retrieved successfully',
            'data' => [
                'sessions' => $tokens,
            ],
        ]);
    }

    /**
     * Revoke session (API).
     */
    public function revokeSessionApi(Request $request, $sessionId)
    {
        $user = $request->user();

        $deleted = DB::table('personal_access_tokens')
            ->where('id', $sessionId)
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', User::class)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Session revoked successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Session not found',
        ], 404);
    }

    /**
     * Revoke all sessions (API).
     */
    public function revokeAllSessionsApi(Request $request)
    {
        $user = $request->user();
        $currentToken = $request->user()->currentAccessToken();

        // Delete all tokens except the current one
        DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', User::class)
            ->where('id', '!=', $currentToken->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All other sessions have been revoked',
        ]);
    }

    /**
     * Update notifications (API).
     */
    public function updateNotificationsApi(Request $request)
    {
        $user = $request->user();
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

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated',
            'data' => [
                'notifications' => $preferences,
            ],
        ]);
    }

    /**
     * Update appearance (API).
     */
    public function updateAppearanceApi(Request $request)
    {
        $user = $request->user();
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

        return response()->json([
            'success' => true,
            'message' => 'Appearance settings updated',
            'data' => [
                'appearance' => $validated,
            ],
        ]);
    }

    /**
     * Update privacy (API).
     */
    public function updatePrivacyApi(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

        $settings = [
            'profile_visibility' => $request->input('profile_visibility', 'family'),
            'activity_tracking' => $request->boolean('activity_tracking'),
            'share_analytics' => $request->boolean('share_analytics'),
        ];

        foreach ($settings as $key => $value) {
            $tenant->setSetting('privacy.' . $key, $value);
        }

        return response()->json([
            'success' => true,
            'message' => 'Privacy settings updated',
            'data' => [
                'privacy' => $settings,
            ],
        ]);
    }

    /**
     * Export data (API).
     */
    public function exportDataApi(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;

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

        return response()->json([
            'success' => true,
            'message' => 'Data exported successfully',
            'data' => $data,
        ]);
    }

    /**
     * Request account deletion (API).
     */
    public function requestAccountDeletionApi(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The password is incorrect.');
                }
            }],
            'confirmation' => 'required|in:DELETE',
        ]);

        $user->tenant->setSetting('deletion_requested_at', now()->toIso8601String());
        $user->tenant->setSetting('deletion_requested_by', $user->id);

        // Revoke the current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Your account deletion has been requested. Your data will be permanently deleted within 30 days.',
        ]);
    }

    /**
     * Get login activity (API).
     */
    public function getLoginActivityApi(Request $request)
    {
        $user = $request->user();

        $loginAttempts = DB::table('login_attempts')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'auth_method' => $attempt->auth_method ?? 'password',
                    'ip_address' => $attempt->ip_address,
                    'successful' => (bool) $attempt->successful,
                    'created_at' => $attempt->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Login activity retrieved successfully',
            'data' => [
                'login_activity' => $loginAttempts,
            ],
        ]);
    }

    /**
     * Generate recovery code (API).
     */
    public function generateRecoveryCodeApi(Request $request)
    {
        $code = User::generateRecoveryCode();

        return response()->json([
            'success' => true,
            'message' => 'Recovery code generated',
            'data' => [
                'code' => $code,
            ],
        ]);
    }

    /**
     * Save recovery code (API).
     */
    public function saveRecoveryCodeApi(Request $request)
    {
        $user = $request->user();

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

        // Send notification email
        Mail::to($user->email)->send(new RecoveryCodeSetMail(
            $user->name,
            $request->recovery_code,
            $isUpdate ? 'updated' : 'set'
        ));

        return response()->json([
            'success' => true,
            'message' => 'Account recovery code has been ' . ($isUpdate ? 'updated' : 'set') . ' successfully.',
        ]);
    }

    /**
     * Get social accounts (API).
     */
    public function getSocialAccountsApi(Request $request)
    {
        $user = $request->user();
        $socialAccounts = SocialAccount::where('user_id', $user->id)->get();

        $providers = ['google', 'apple', 'facebook'];
        $accounts = [];

        foreach ($providers as $provider) {
            $connected = $socialAccounts->firstWhere('provider', $provider);
            $accounts[] = [
                'provider' => $provider,
                'connected' => (bool) $connected,
                'connected_at' => $connected ? $connected->created_at?->toIso8601String() : null,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Social accounts retrieved successfully',
            'data' => [
                'social_accounts' => $accounts,
            ],
        ]);
    }

    /**
     * Disconnect social account (API).
     */
    public function disconnectSocialApi(Request $request, string $provider)
    {
        $user = $request->user();

        $deleted = SocialAccount::where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($provider) . ' account disconnected successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Social account not found',
        ], 404);
    }
}
