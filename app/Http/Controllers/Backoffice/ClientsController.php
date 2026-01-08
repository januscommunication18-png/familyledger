<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\FamilyMember;
use App\Models\Backoffice\ViewCode;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

        // Check if admin has valid view access
        $hasViewAccess = session('backoffice_view_access_' . $client->id, false);

        return view('backoffice.clients.show', compact('client', 'stats', 'hasViewAccess'));
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
     * Request view code for accessing client data.
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
     * Show client data (protected by view code).
     */
    public function showData(Tenant $client): View|RedirectResponse
    {
        // Check if admin has view access
        if (!session('backoffice_view_access_' . $client->id)) {
            return redirect()->route('backoffice.clients.show', $client)
                ->withErrors(['access' => 'You need to verify your identity to view client data.']);
        }

        $admin = Auth::guard('backoffice')->user();

        // Get client data
        $users = User::where('tenant_id', $client->id)->get();
        $familyMembers = FamilyMember::where('tenant_id', $client->id)->get();

        $admin->logActivity(ActivityLog::ACTION_VIEW_CLIENT, $client->id, 'Viewed full client data');

        return view('backoffice.clients.data', compact('client', 'users', 'familyMembers'));
    }

    /**
     * Revoke view access (called when leaving the page).
     */
    public function revokeViewAccess(Tenant $client): JsonResponse
    {
        session()->forget('backoffice_view_access_' . $client->id);

        return response()->json(['success' => true]);
    }
}
