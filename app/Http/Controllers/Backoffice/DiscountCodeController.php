<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\DiscountCode;
use App\Models\PackagePlan;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DiscountCodeController extends Controller
{
    /**
     * Display a listing of discount codes.
     */
    public function index(Request $request): View
    {
        $query = DiscountCode::with('packagePlan');

        // Filter by plan type
        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Filter by package plan
        if ($request->filled('package_plan_id')) {
            $query->where('package_plan_id', $request->package_plan_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $discountCodes = $query->latest()->paginate(20);
        $packagePlans = PackagePlan::active()->ordered()->get();

        return view('backoffice.discount-codes.index', compact('discountCodes', 'packagePlans'));
    }

    /**
     * Show the form for creating a new discount code.
     */
    public function create(): View
    {
        $packagePlans = PackagePlan::active()->ordered()->get();

        return view('backoffice.discount-codes.create', compact('packagePlans'));
    }

    /**
     * Store a newly created discount code.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:discount_codes,code',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'plan_type' => 'required|in:monthly,yearly,both',
            'package_plan_id' => 'nullable|exists:package_plans,id',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper($validated['code']);

        $discountCode = DiscountCode::create($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_CREATE_DISCOUNT, null, 'Created discount code: ' . $discountCode->code);

        return redirect()->route('backoffice.discount-codes.index')
            ->with('message', 'Discount code created successfully.');
    }

    /**
     * Display the specified discount code.
     */
    public function show(DiscountCode $discountCode): View
    {
        $discountCode->load('packagePlan');

        return view('backoffice.discount-codes.show', compact('discountCode'));
    }

    /**
     * Show the form for editing the specified discount code.
     */
    public function edit(DiscountCode $discountCode): View
    {
        $packagePlans = PackagePlan::active()->ordered()->get();

        return view('backoffice.discount-codes.edit', compact('discountCode', 'packagePlans'));
    }

    /**
     * Update the specified discount code.
     */
    public function update(Request $request, DiscountCode $discountCode): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:discount_codes,code,' . $discountCode->id,
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'plan_type' => 'required|in:monthly,yearly,both',
            'package_plan_id' => 'nullable|exists:package_plans,id',
            'max_uses' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['code'] = strtoupper($validated['code']);

        $discountCode->update($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_UPDATE_DISCOUNT, null, 'Updated discount code: ' . $discountCode->code);

        return redirect()->route('backoffice.discount-codes.index')
            ->with('message', 'Discount code updated successfully.');
    }

    /**
     * Remove the specified discount code.
     */
    public function destroy(DiscountCode $discountCode): RedirectResponse
    {
        $code = $discountCode->code;
        $discountCode->delete();

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_DELETE_DISCOUNT, null, 'Deleted discount code: ' . $code);

        return redirect()->route('backoffice.discount-codes.index')
            ->with('message', 'Discount code deleted successfully.');
    }

    /**
     * Toggle the active status of a discount code.
     */
    public function toggleStatus(DiscountCode $discountCode): RedirectResponse
    {
        $discountCode->update([
            'is_active' => !$discountCode->is_active,
        ]);

        Auth::guard('backoffice')->user()
            ->logActivity(
                ActivityLog::ACTION_UPDATE_DISCOUNT,
                null,
                'Toggled discount code status: ' . $discountCode->code . ' to ' . ($discountCode->is_active ? 'active' : 'inactive')
            );

        return back()->with('message', 'Discount code status updated successfully.');
    }

    /**
     * Generate a random discount code.
     */
    public function generateCode(): \Illuminate\Http\JsonResponse
    {
        $code = strtoupper(Str::random(8));

        // Make sure it's unique
        while (DiscountCode::where('code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }

        return response()->json(['code' => $code]);
    }
}
