<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    /**
     * Show profile settings.
     */
    public function profile(): View
    {
        $admin = Auth::guard('backoffice')->user();
        return view('backoffice.settings.profile', compact('admin'));
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = Auth::guard('backoffice')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:backoffice_admins,email,' . $admin->id,
        ]);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $admin->logActivity(ActivityLog::ACTION_UPDATE_PROFILE);

        return back()->with('message', 'Profile updated successfully.');
    }

    /**
     * Show change password form.
     */
    public function changePassword(): View
    {
        return view('backoffice.settings.change-password');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $admin = Auth::guard('backoffice')->user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        $admin->logActivity(ActivityLog::ACTION_CHANGE_PASSWORD);

        return back()->with('message', 'Password changed successfully.');
    }
}
