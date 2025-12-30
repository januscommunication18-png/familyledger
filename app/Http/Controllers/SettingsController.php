<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        return view('pages.settings.index');
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

        return redirect()->route('settings.index')
            ->with('success', 'Profile updated successfully');
    }
}
