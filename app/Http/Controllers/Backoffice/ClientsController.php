<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\FamilyMember;
use App\Models\Backoffice\ViewCode;
use App\Models\Backoffice\ActivityLog;
use App\Models\Backoffice\DataAccessRequest;
use App\Mail\DataAccessRequestMail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ClientsController extends Controller
{
    /**
     * Show clients list.
     */
    public function index(Request $request): View
    {
        $query = Tenant::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(20);

        // Get counts and owner for each client
        foreach ($clients as $client) {
            $client->users_count = User::where('tenant_id', $client->id)->count();
            $client->family_members_count = FamilyMember::where('tenant_id', $client->id)->count();
            // Get first user (owner) - the one who registered first
            $client->owner = User::where('tenant_id', $client->id)->orderBy('created_at')->first();
        }

        return view('backoffice.clients.index', compact('clients'));
    }

    /**
     * Show client details (basic info only, no PII).
     */
    public function show(Tenant $client): View
    {
        $admin = Auth::guard('backoffice')->user();

        // Log the view action
        $admin->logActivity(ActivityLog::ACTION_VIEW_CLIENT, $client->id);

        // Get counts only (no actual data)
        $stats = [
            'users_count' => User::where('tenant_id', $client->id)->count(),
            'family_members_count' => FamilyMember::where('tenant_id', $client->id)->count(),
        ];

        // Check if admin has valid view access via approved data access request
        $activeRequest = DataAccessRequest::findActiveForAdminAndTenant($admin->id, $client->id);
        $hasViewAccess = $activeRequest && $activeRequest->hasValidAccess();

        // Get owner for sending access request email
        $owner = User::where('tenant_id', $client->id)->orderBy('created_at')->first();

        return view('backoffice.clients.show', compact('client', 'stats', 'hasViewAccess', 'activeRequest', 'owner'));
    }

    /**
     * Toggle client active status.
     */
    public function toggleStatus(Request $request, Tenant $client): RedirectResponse
    {
        $admin = Auth::guard('backoffice')->user();

        $client->update([
            'is_active' => !$client->is_active,
        ]);

        $admin->logActivity(
            ActivityLog::ACTION_TOGGLE_CLIENT_STATUS,
            $client->id,
            'Status changed to: ' . ($client->is_active ? 'active' : 'inactive')
        );

        return back()->with('message', 'Client status updated successfully.');
    }

    /**
     * Request data access permission from client.
     */
    public function requestDataAccess(Request $request, Tenant $client): JsonResponse
    {
        $admin = Auth::guard('backoffice')->user();

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Check if there's already an active request
        $existingRequest = DataAccessRequest::findActiveForAdminAndTenant($admin->id, $client->id);
        if ($existingRequest) {
            if ($existingRequest->hasValidAccess()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have active access to this client\'s data.',
                ], 422);
            }
            if ($existingRequest->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'There is already a pending access request for this client.',
                ], 422);
            }
        }

        // Get the account owner
        $owner = User::where('tenant_id', $client->id)->orderBy('created_at')->first();
        if (!$owner || !$owner->email) {
            return response()->json([
                'success' => false,
                'message' => 'Could not find a valid email address for this client.',
            ], 422);
        }

        // Create the access request
        $accessRequest = DataAccessRequest::create([
            'admin_id' => $admin->id,
            'tenant_id' => $client->id,
            'reason' => $request->reason,
            'status' => DataAccessRequest::STATUS_PENDING,
            'ip_address' => request()->ip(),
        ]);

        // Send email to client
        Mail::to($owner->email)->send(new DataAccessRequestMail($accessRequest, $owner->name ?? 'Family Ledger User'));

        $admin->logActivity(
            ActivityLog::ACTION_REQUEST_VIEW_CODE,
            $client->id,
            'Sent data access request to ' . $owner->email
        );

        return response()->json([
            'success' => true,
            'message' => 'Access request sent to ' . $owner->email . '. You will be notified when they respond.',
            'request_id' => $accessRequest->id,
        ]);
    }

    /**
     * Check status of data access request.
     */
    public function checkDataAccessStatus(Tenant $client): JsonResponse
    {
        $admin = Auth::guard('backoffice')->user();

        $activeRequest = DataAccessRequest::findActiveForAdminAndTenant($admin->id, $client->id);

        if (!$activeRequest) {
            return response()->json([
                'status' => 'none',
                'message' => 'No active request found.',
            ]);
        }

        if ($activeRequest->hasValidAccess()) {
            return response()->json([
                'status' => 'approved',
                'message' => 'Access granted until ' . $activeRequest->access_expires_at->format('M j, Y g:i A'),
                'expires_at' => $activeRequest->access_expires_at->toIso8601String(),
            ]);
        }

        if ($activeRequest->isPending()) {
            return response()->json([
                'status' => 'pending',
                'message' => 'Waiting for client approval. Request expires ' . $activeRequest->expires_at->format('M j, Y g:i A'),
                'expires_at' => $activeRequest->expires_at->toIso8601String(),
            ]);
        }

        return response()->json([
            'status' => $activeRequest->status,
            'message' => 'Request status: ' . $activeRequest->status,
        ]);
    }

    /**
     * Request view code for accessing client data.
     * @deprecated Use requestDataAccess instead
     */
    public function requestViewCode(Tenant $client): JsonResponse
    {
        $admin = Auth::guard('backoffice')->user();

        // Generate a new view code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        ViewCode::create([
            'admin_id' => $admin->id,
            'tenant_id' => $client->id,
            'code' => bcrypt($code),
            'expires_at' => now()->addMinutes(5),
            'ip_address' => request()->ip(),
        ]);

        $admin->logActivity(ActivityLog::ACTION_REQUEST_VIEW_CODE, $client->id);

        // In production, send email. For now, return code directly.
        // Mail::to($admin->email)->send(new ViewCodeMail($code, $client));

        return response()->json([
            'success' => true,
            'message' => 'View code has been sent to your email.',
            'code_debug' => $code, // Remove in production
        ]);
    }

    /**
     * Verify view code and grant access.
     */
    public function verifyViewCode(Request $request, Tenant $client): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $admin = Auth::guard('backoffice')->user();

        // Find the most recent valid code for this admin/tenant
        $viewCode = ViewCode::where('admin_id', $admin->id)
            ->where('tenant_id', $client->id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$viewCode || !$viewCode->verify($request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired code.',
            ], 422);
        }

        // Mark code as used
        $viewCode->markAsUsed();

        // Grant session access
        session(['backoffice_view_access_' . $client->id => true]);

        $admin->logActivity(ActivityLog::ACTION_VERIFY_VIEW_CODE, $client->id);

        return response()->json([
            'success' => true,
            'message' => 'Access granted.',
        ]);
    }

    /**
     * Show client data (protected by data access request approval).
     */
    public function showData(Tenant $client): View|RedirectResponse
    {
        $admin = Auth::guard('backoffice')->user();

        // Check if admin has valid data access via approved request
        $activeRequest = DataAccessRequest::findActiveForAdminAndTenant($admin->id, $client->id);

        if (!$activeRequest || !$activeRequest->hasValidAccess()) {
            return redirect()->route('backoffice.clients.show', $client)
                ->withErrors(['access' => 'You need client approval to view their data. Please send an access request.']);
        }

        $tenantId = $client->id;

        // Get all client data
        $users = User::where('tenant_id', $tenantId)->get();
        $familyMembers = FamilyMember::where('tenant_id', $tenantId)->get();
        $pets = \App\Models\Pet::where('tenant_id', $tenantId)->get();
        $assets = \App\Models\Asset::where('tenant_id', $tenantId)->get();
        $insurancePolicies = \App\Models\InsurancePolicy::where('tenant_id', $tenantId)->get();
        $legalDocuments = \App\Models\LegalDocument::where('tenant_id', $tenantId)->get();
        $taxReturns = \App\Models\TaxReturn::where('tenant_id', $tenantId)->get();
        $budgets = \App\Models\Budget::where('tenant_id', $tenantId)->get();
        $goals = \App\Models\Goal::where('tenant_id', $tenantId)->get();
        $persons = \App\Models\Person::where('tenant_id', $tenantId)->get();
        $journalEntries = \App\Models\JournalEntry::where('tenant_id', $tenantId)->latest()->take(50)->get();
        $familyCircles = \App\Models\FamilyCircle::where('tenant_id', $tenantId)->get();
        $familyResources = \App\Models\FamilyResource::where('tenant_id', $tenantId)->get();
        $shoppingLists = \App\Models\ShoppingList::where('tenant_id', $tenantId)->get();
        $todoLists = \App\Models\TodoList::where('tenant_id', $tenantId)->get();
        $invoices = \App\Models\Invoice::where('tenant_id', $tenantId)->latest()->get();

        $admin->logActivity(ActivityLog::ACTION_VIEW_CLIENT, $client->id, 'Viewed full client data');

        return view('backoffice.clients.data', compact(
            'client',
            'activeRequest',
            'users',
            'familyMembers',
            'pets',
            'assets',
            'insurancePolicies',
            'legalDocuments',
            'taxReturns',
            'budgets',
            'goals',
            'persons',
            'journalEntries',
            'familyCircles',
            'familyResources',
            'shoppingLists',
            'todoLists',
            'invoices'
        ));
    }

    /**
     * Revoke view access (called when leaving the page).
     */
    public function revokeViewAccess(Tenant $client): JsonResponse
    {
        session()->forget('backoffice_view_access_' . $client->id);

        return response()->json(['success' => true]);
    }

    /**
     * Delete client data only (keep users and tenant).
     */
    public function destroyData(Request $request, Tenant $client): RedirectResponse
    {
        $request->validate([
            'confirmation' => 'required|string|in:DELETE',
        ], [
            'confirmation.in' => 'You must type DELETE to confirm.',
        ]);

        $admin = Auth::guard('backoffice')->user();
        $clientId = $client->id;
        $clientName = $client->name;

        // Log the action before deletion
        $admin->logActivity(
            ActivityLog::ACTION_DELETE_CLIENT,
            $clientId,
            'Deleted data for client: ' . $clientName . ' (users and tenant kept)'
        );

        // Delete all data
        $this->deleteClientData($clientId);

        return redirect()->route('backoffice.clients.show', $client)
            ->with('message', 'All data for client "' . $clientName . '" has been permanently deleted. User accounts and tenant remain intact.');
    }

    /**
     * Delete client completely (including users and tenant).
     */
    public function destroy(Request $request, Tenant $client): RedirectResponse
    {
        $request->validate([
            'confirmation' => 'required|string|in:DELETE FOREVER',
        ], [
            'confirmation.in' => 'You must type DELETE FOREVER to confirm.',
        ]);

        $admin = Auth::guard('backoffice')->user();
        $clientId = $client->id;
        $clientName = $client->name;

        // Log the action before deletion
        $admin->logActivity(
            ActivityLog::ACTION_DELETE_CLIENT,
            $clientId,
            'Permanently deleted client: ' . $clientName . ' (including all users and tenant)'
        );

        // Delete all data first
        $this->deleteClientData($clientId);

        // Delete users (with avatar cleanup)
        $users = User::where('tenant_id', $clientId)->get();
        foreach ($users as $user) {
            if ($user->avatar) {
                \Storage::disk('do_spaces')->delete($user->avatar);
            }
            $user->socialAccounts()->delete();
            $user->delete();
        }

        // Finally, delete the tenant
        $client->delete();

        return redirect()->route('backoffice.clients.index')
            ->with('message', 'Client "' . $clientName . '" and all associated records have been permanently deleted.');
    }

    /**
     * Helper method to delete all client data (without users/tenant).
     */
    private function deleteClientData(string $clientId): void
    {
        // Delete invoices
        \App\Models\Invoice::where('tenant_id', $clientId)->delete();

        // Delete coparenting records (with file cleanup)
        $coparentAttachments = \App\Models\CoparentMessageAttachment::where('tenant_id', $clientId)->get();
        foreach ($coparentAttachments as $attachment) {
            if ($attachment->file_path) {
                \Storage::disk('do_spaces')->delete($attachment->file_path);
            }
        }
        \App\Models\CoparentMessageAttachment::where('tenant_id', $clientId)->delete();
        \App\Models\CoparentMessageEdit::where('tenant_id', $clientId)->delete();
        \App\Models\CoparentMessage::where('tenant_id', $clientId)->delete();
        \App\Models\CoparentConversation::where('tenant_id', $clientId)->delete();
        \App\Models\CoparentMessageTemplate::where('tenant_id', $clientId)->delete();
        \App\Models\CoparentingActivity::where('tenant_id', $clientId)->delete();
        \App\Models\CoparentingActualTime::where('tenant_id', $clientId)->delete();
        \App\Models\CoparentingSchedule::where('tenant_id', $clientId)->delete();
        \App\Models\PendingCoparentEdit::where('tenant_id', $clientId)->delete();
        \App\Models\SharedExpensePayment::where('tenant_id', $clientId)->delete();
        \App\Models\ConflictResolution::where('tenant_id', $clientId)->delete();

        // Delete member-related records
        \App\Models\MemberAllergy::where('tenant_id', $clientId)->delete();
        \App\Models\MemberAuditLog::where('tenant_id', $clientId)->delete();
        \App\Models\MemberContact::where('tenant_id', $clientId)->delete();
        \App\Models\MemberDocument::where('tenant_id', $clientId)->delete();
        \App\Models\MemberEducationDocument::where('tenant_id', $clientId)->delete();
        \App\Models\MemberHealthcareProvider::where('tenant_id', $clientId)->delete();
        \App\Models\MemberMedicalCondition::where('tenant_id', $clientId)->delete();
        \App\Models\MemberMedicalInfo::where('tenant_id', $clientId)->delete();
        \App\Models\MemberMedication::where('tenant_id', $clientId)->delete();
        \App\Models\MemberSchoolInfo::where('tenant_id', $clientId)->delete();
        \App\Models\MemberVaccination::where('tenant_id', $clientId)->delete();

        // Delete family-related records
        \App\Models\FamilyCircle::where('tenant_id', $clientId)->delete();
        \App\Models\FamilyMember::where('tenant_id', $clientId)->delete();
        \App\Models\FamilyResource::where('tenant_id', $clientId)->delete();

        // Delete budget records
        \App\Models\BudgetTransaction::where('tenant_id', $clientId)->delete();
        \App\Models\Budget::where('tenant_id', $clientId)->delete();

        // Delete goal records
        \App\Models\GoalCheckIn::where('tenant_id', $clientId)->delete();
        \App\Models\GoalTemplate::where('tenant_id', $clientId)->delete();
        \App\Models\Goal::where('tenant_id', $clientId)->delete();

        // Delete asset records (with file cleanup)
        $assetDocuments = \App\Models\AssetDocument::where('tenant_id', $clientId)->get();
        foreach ($assetDocuments as $doc) {
            if ($doc->file_path) {
                \Storage::disk('do_spaces')->delete($doc->file_path);
            }
        }
        \App\Models\AssetDocument::where('tenant_id', $clientId)->delete();
        \App\Models\AssetOwner::where('tenant_id', $clientId)->delete();
        \App\Models\Asset::where('tenant_id', $clientId)->delete();

        // Delete insurance policies
        \App\Models\InsurancePolicy::where('tenant_id', $clientId)->delete();

        // Delete legal documents
        \App\Models\LegalDocument::where('tenant_id', $clientId)->delete();

        // Delete tax returns
        \App\Models\TaxReturn::where('tenant_id', $clientId)->delete();

        // Delete person records (with file cleanup)
        $personAttachments = \App\Models\PersonAttachment::where('tenant_id', $clientId)->get();
        foreach ($personAttachments as $attachment) {
            if ($attachment->file_path) {
                \Storage::disk('do_spaces')->delete($attachment->file_path);
            }
        }
        \App\Models\PersonAttachment::where('tenant_id', $clientId)->delete();
        \App\Models\Person::where('tenant_id', $clientId)->delete();

        // Delete pet records
        \App\Models\PetMedication::where('tenant_id', $clientId)->delete();
        \App\Models\PetVaccination::where('tenant_id', $clientId)->delete();
        \App\Models\Pet::where('tenant_id', $clientId)->delete();

        // Delete journal records (with file cleanup)
        $journalAttachments = \App\Models\JournalAttachment::where('tenant_id', $clientId)->get();
        foreach ($journalAttachments as $attachment) {
            if ($attachment->file_path) {
                \Storage::disk('do_spaces')->delete($attachment->file_path);
            }
            if ($attachment->thumbnail_path) {
                \Storage::disk('do_spaces')->delete($attachment->thumbnail_path);
            }
        }
        \App\Models\JournalAttachment::where('tenant_id', $clientId)->delete();
        \App\Models\JournalTag::where('tenant_id', $clientId)->delete();
        \App\Models\JournalEntry::where('tenant_id', $clientId)->delete();

        // Delete shopping lists and items
        \App\Models\ShoppingItemHistory::where('tenant_id', $clientId)->delete();
        \App\Models\ShoppingItem::where('tenant_id', $clientId)->delete();
        \App\Models\ShoppingList::where('tenant_id', $clientId)->delete();

        // Delete todo records
        \App\Models\TaskOccurrence::where('tenant_id', $clientId)->delete();
        \App\Models\TodoList::where('tenant_id', $clientId)->delete();
        \App\Models\TodoItem::where('tenant_id', $clientId)->delete();

        // Delete invitations and collaborators
        \App\Models\Invitation::where('tenant_id', $clientId)->delete();
        \App\Models\Collaborator::where('tenant_id', $clientId)->delete();
        \App\Models\CollaboratorInvite::where('tenant_id', $clientId)->delete();

        // Delete sync logs
        \App\Models\SyncLog::where('tenant_id', $clientId)->delete();
    }
}
