<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\PackagePlan;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PackagePlanController extends Controller
{
    /**
     * Display a listing of package plans.
     */
    public function index(Request $request): View
    {
        $query = PackagePlan::query();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $plans = $query->ordered()->paginate(20);

        return view('backoffice.package-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new package plan.
     */
    public function create(): View
    {
        return view('backoffice.package-plans.create');
    }

    /**
     * Store a newly created package plan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:free,paid',
            'description' => 'nullable|string',
            'trial_period_days' => 'required|integer|min:0',
            'cost_per_month' => 'required|numeric|min:0',
            'cost_per_year' => 'required|numeric|min:0',
            'family_circles_limit' => 'required|integer|min:0',
            'family_members_limit' => 'required|integer|min:0',
            'document_storage_limit' => 'required|integer|min:0',
            'reminder_features' => 'nullable|array',
            'reminder_features.*' => 'in:push_notification,email_reminder,sms_reminder',
            'paddle_product_id' => 'nullable|string|max:255',
            'paddle_monthly_price_id' => 'nullable|string|max:255',
            'paddle_yearly_price_id' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $plan = PackagePlan::create($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_CREATE_PLAN, null, 'Created package plan: ' . $plan->name);

        return redirect()->route('backoffice.package-plans.index')
            ->with('message', 'Package plan created successfully.');
    }

    /**
     * Display the specified package plan.
     */
    public function show(PackagePlan $packagePlan): View
    {
        $packagePlan->load('discountCodes');

        return view('backoffice.package-plans.show', compact('packagePlan'));
    }

    /**
     * Show the form for editing the specified package plan.
     */
    public function edit(PackagePlan $packagePlan): View
    {
        return view('backoffice.package-plans.edit', compact('packagePlan'));
    }

    /**
     * Update the specified package plan.
     */
    public function update(Request $request, PackagePlan $packagePlan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:free,paid',
            'description' => 'nullable|string',
            'trial_period_days' => 'required|integer|min:0',
            'cost_per_month' => 'required|numeric|min:0',
            'cost_per_year' => 'required|numeric|min:0',
            'family_circles_limit' => 'required|integer|min:0',
            'family_members_limit' => 'required|integer|min:0',
            'document_storage_limit' => 'required|integer|min:0',
            'reminder_features' => 'nullable|array',
            'reminder_features.*' => 'in:push_notification,email_reminder,sms_reminder',
            'paddle_product_id' => 'nullable|string|max:255',
            'paddle_monthly_price_id' => 'nullable|string|max:255',
            'paddle_yearly_price_id' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // If reminder_features is not provided, set it to empty array
        if (!isset($validated['reminder_features'])) {
            $validated['reminder_features'] = [];
        }

        $packagePlan->update($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_UPDATE_PLAN, null, 'Updated package plan: ' . $packagePlan->name);

        return redirect()->route('backoffice.package-plans.index')
            ->with('message', 'Package plan updated successfully.');
    }

    /**
     * Remove the specified package plan.
     */
    public function destroy(PackagePlan $packagePlan): RedirectResponse
    {
        $planName = $packagePlan->name;
        $packagePlan->delete();

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_DELETE_PLAN, null, 'Deleted package plan: ' . $planName);

        return redirect()->route('backoffice.package-plans.index')
            ->with('message', 'Package plan deleted successfully.');
    }

    /**
     * Toggle the active status of a package plan.
     */
    public function toggleStatus(PackagePlan $packagePlan): RedirectResponse
    {
        $packagePlan->update([
            'is_active' => !$packagePlan->is_active,
        ]);

        Auth::guard('backoffice')->user()
            ->logActivity(
                ActivityLog::ACTION_UPDATE_PLAN,
                null,
                'Toggled package plan status: ' . $packagePlan->name . ' to ' . ($packagePlan->is_active ? 'active' : 'inactive')
            );

        return back()->with('message', 'Package plan status updated successfully.');
    }
}
