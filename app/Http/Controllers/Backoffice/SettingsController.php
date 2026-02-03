<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Show DB reset page.
     */
    public function dbReset(): View
    {
        // Get counts for display
        $stats = [
            'tenants' => \App\Models\Tenant::count(),
            'users' => \App\Models\User::count(),
            'family_members' => \App\Models\FamilyMember::count(),
            'assets' => \App\Models\Asset::count(),
            'budgets' => \App\Models\Budget::count(),
            'goals' => \App\Models\Goal::count(),
            'journal_entries' => \App\Models\JournalEntry::count(),
            'pets' => \App\Models\Pet::count(),
            'invoices' => \App\Models\Invoice::count(),
        ];

        return view('backoffice.settings.db-reset', compact('stats'));
    }

    /**
     * Perform DB reset - delete all client data but keep users and tenants.
     */
    public function performDbReset(Request $request): RedirectResponse
    {
        $request->validate([
            'confirmation' => 'required|string|in:RESET',
        ], [
            'confirmation.in' => 'You must type RESET to confirm.',
        ]);

        $admin = Auth::guard('backoffice')->user();

        // Log the action before reset
        $admin->logActivity(
            ActivityLog::ACTION_DB_RESET,
            null,
            'Performed full database reset - all client data deleted'
        );

        // Delete all data from all tenants
        // Order matters due to foreign key constraints

        // Delete invoices
        \App\Models\Invoice::query()->delete();

        // Delete coparenting records (with file cleanup)
        $coparentAttachments = \App\Models\CoparentMessageAttachment::all();
        foreach ($coparentAttachments as $attachment) {
            if ($attachment->file_path) {
                \Storage::disk('do_spaces')->delete($attachment->file_path);
            }
        }
        \App\Models\CoparentMessageAttachment::query()->delete();
        \App\Models\CoparentMessageEdit::query()->delete();
        \App\Models\CoparentMessage::query()->delete();
        \App\Models\CoparentConversation::query()->delete();
        \App\Models\CoparentMessageTemplate::query()->delete();
        \App\Models\CoparentingActivity::query()->delete();
        \App\Models\CoparentingActualTime::query()->delete();
        \App\Models\CoparentingSchedule::query()->delete();
        \App\Models\PendingCoparentEdit::query()->delete();
        \App\Models\SharedExpensePayment::query()->delete();
        \App\Models\ConflictResolution::query()->delete();

        // Delete member-related records
        \App\Models\MemberAllergy::query()->delete();
        \App\Models\MemberAuditLog::query()->delete();
        \App\Models\MemberContact::query()->delete();
        \App\Models\MemberDocument::query()->delete();
        \App\Models\MemberEducationDocument::query()->delete();
        \App\Models\MemberHealthcareProvider::query()->delete();
        \App\Models\MemberMedicalCondition::query()->delete();
        \App\Models\MemberMedicalInfo::query()->delete();
        \App\Models\MemberMedication::query()->delete();
        \App\Models\MemberSchoolInfo::query()->delete();
        \App\Models\MemberVaccination::query()->delete();

        // Delete family-related records
        \App\Models\FamilyCircle::query()->delete();
        \App\Models\FamilyMember::query()->delete();
        \App\Models\FamilyResource::query()->delete();

        // Delete budget records
        \App\Models\BudgetTransaction::query()->delete();
        \App\Models\Budget::query()->delete();

        // Delete goal records
        \App\Models\GoalCheckIn::query()->delete();
        \App\Models\GoalTemplate::query()->delete();
        \App\Models\Goal::query()->delete();

        // Delete asset records (with file cleanup)
        $assetDocuments = \App\Models\AssetDocument::all();
        foreach ($assetDocuments as $doc) {
            if ($doc->file_path) {
                \Storage::disk('do_spaces')->delete($doc->file_path);
            }
        }
        \App\Models\AssetDocument::query()->delete();
        \App\Models\AssetOwner::query()->delete();
        \App\Models\Asset::query()->delete();

        // Delete insurance policies
        \App\Models\InsurancePolicy::query()->delete();

        // Delete legal documents
        \App\Models\LegalDocument::query()->delete();

        // Delete tax returns
        \App\Models\TaxReturn::query()->delete();

        // Delete person records (with file cleanup)
        $personAttachments = \App\Models\PersonAttachment::all();
        foreach ($personAttachments as $attachment) {
            if ($attachment->file_path) {
                \Storage::disk('do_spaces')->delete($attachment->file_path);
            }
        }
        \App\Models\PersonAttachment::query()->delete();
        \App\Models\Person::query()->delete();

        // Delete pet records
        \App\Models\PetMedication::query()->delete();
        \App\Models\PetVaccination::query()->delete();
        \App\Models\Pet::query()->delete();

        // Delete journal records (with file cleanup)
        $journalAttachments = \App\Models\JournalAttachment::all();
        foreach ($journalAttachments as $attachment) {
            if ($attachment->file_path) {
                \Storage::disk('do_spaces')->delete($attachment->file_path);
            }
            if ($attachment->thumbnail_path) {
                \Storage::disk('do_spaces')->delete($attachment->thumbnail_path);
            }
        }
        \App\Models\JournalAttachment::query()->delete();
        \App\Models\JournalTag::query()->delete();
        \App\Models\JournalEntry::query()->delete();

        // Delete shopping lists and items
        \App\Models\ShoppingItemHistory::query()->delete();
        \App\Models\ShoppingItem::query()->delete();
        \App\Models\ShoppingList::query()->delete();

        // Delete todo records
        \App\Models\TaskOccurrence::query()->delete();
        \App\Models\TodoList::query()->delete();
        \App\Models\TodoItem::query()->delete();

        // Delete invitations and collaborators
        \App\Models\Invitation::query()->delete();
        \App\Models\Collaborator::query()->delete();
        \App\Models\CollaboratorInvite::query()->delete();

        // Delete sync logs
        \App\Models\SyncLog::query()->delete();

        // Delete users (with avatar cleanup)
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if ($user->avatar) {
                Storage::disk('do_spaces')->delete($user->avatar);
            }
            $user->socialAccounts()->delete();
        }
        \App\Models\User::query()->delete();

        // Delete tenants
        \App\Models\Tenant::query()->delete();

        return redirect()->route('backoffice.settings.dbReset')
            ->with('message', 'Database has been reset successfully. All client data, users, and tenants have been permanently deleted.');
    }
}
