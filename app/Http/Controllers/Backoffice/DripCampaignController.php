<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Backoffice\DripCampaign;
use App\Models\Backoffice\DripEmailStep;
use App\Models\Backoffice\DripEmailLog;
use App\Models\Backoffice\ActivityLog;
use App\Mail\DripEmail;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DripCampaignController extends Controller
{
    /**
     * Display a listing of drip campaigns.
     */
    public function index(Request $request): View
    {
        $query = DripCampaign::withCount('steps', 'logs');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by trigger type
        if ($request->filled('trigger')) {
            $query->where('trigger_type', $request->trigger);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $campaigns = $query->latest()->paginate(20);

        return view('backoffice.drip-campaigns.index', [
            'campaigns' => $campaigns,
            'triggerTypes' => DripCampaign::getTriggerTypes(),
            'statuses' => DripCampaign::getStatuses(),
        ]);
    }

    /**
     * Show the form for creating a new drip campaign.
     */
    public function create(): View
    {
        return view('backoffice.drip-campaigns.create', [
            'triggerTypes' => DripCampaign::getTriggerTypes(),
            'statuses' => DripCampaign::getStatuses(),
        ]);
    }

    /**
     * Store a newly created drip campaign.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:signup,trial_expiring,custom',
            'status' => 'required|in:draft,active,paused',
            'delay_days' => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
        ]);

        $validated['created_by'] = Auth::guard('backoffice')->id();

        $campaign = DripCampaign::create($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_CREATE_DRIP_CAMPAIGN, null, 'Created drip campaign: ' . $campaign->name);

        return redirect()->route('backoffice.drip-campaigns.edit', $campaign)
            ->with('message', 'Drip campaign created successfully. Now add email steps.');
    }

    /**
     * Display the specified drip campaign.
     */
    public function show(DripCampaign $campaign): View
    {
        $campaign->load(['steps', 'creator']);

        $recentLogs = $campaign->logs()
            ->with(['step', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'total_sent' => $campaign->logs()->whereIn('status', ['sent', 'opened', 'clicked'])->count(),
            'opened' => $campaign->logs()->whereNotNull('opened_at')->count(),
            'clicked' => $campaign->logs()->whereNotNull('clicked_at')->count(),
            'failed' => $campaign->logs()->where('status', 'failed')->count(),
        ];

        return view('backoffice.drip-campaigns.show', compact('campaign', 'recentLogs', 'stats'));
    }

    /**
     * Show the form for editing the specified drip campaign.
     */
    public function edit(DripCampaign $campaign): View
    {
        $campaign->load('steps');

        return view('backoffice.drip-campaigns.edit', [
            'campaign' => $campaign,
            'triggerTypes' => DripCampaign::getTriggerTypes(),
            'statuses' => DripCampaign::getStatuses(),
        ]);
    }

    /**
     * Update the specified drip campaign.
     */
    public function update(Request $request, DripCampaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:signup,trial_expiring,custom',
            'status' => 'required|in:draft,active,paused',
            'delay_days' => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
        ]);

        $campaign->update($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_UPDATE_DRIP_CAMPAIGN, null, 'Updated drip campaign: ' . $campaign->name);

        return redirect()->route('backoffice.drip-campaigns.edit', $campaign)
            ->with('message', 'Drip campaign updated successfully.');
    }

    /**
     * Remove the specified drip campaign.
     */
    public function destroy(DripCampaign $campaign): RedirectResponse
    {
        $campaignName = $campaign->name;
        $campaign->delete();

        Auth::guard('backoffice')->user()
            ->logActivity(ActivityLog::ACTION_DELETE_DRIP_CAMPAIGN, null, 'Deleted drip campaign: ' . $campaignName);

        return redirect()->route('backoffice.drip-campaigns.index')
            ->with('message', 'Drip campaign deleted successfully.');
    }

    /**
     * Toggle the status of a drip campaign.
     */
    public function toggleStatus(Request $request, DripCampaign $campaign): RedirectResponse
    {
        $newStatus = $request->input('status');

        if (!in_array($newStatus, ['draft', 'active', 'paused'])) {
            $newStatus = $campaign->status === 'active' ? 'paused' : 'active';
        }

        $campaign->update(['status' => $newStatus]);

        Auth::guard('backoffice')->user()
            ->logActivity(
                ActivityLog::ACTION_UPDATE_DRIP_CAMPAIGN,
                null,
                'Changed drip campaign status: ' . $campaign->name . ' to ' . $newStatus
            );

        return back()->with('message', 'Campaign status updated to ' . $newStatus . '.');
    }

    /**
     * View all logs for a campaign.
     */
    public function logs(Request $request, DripCampaign $campaign): View
    {
        $query = $campaign->logs()->with(['step', 'user', 'tenant']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by email
        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        $logs = $query->latest()->paginate(50);

        return view('backoffice.drip-campaigns.logs', [
            'campaign' => $campaign,
            'logs' => $logs,
            'statuses' => DripEmailLog::getStatuses(),
        ]);
    }

    /**
     * Send a test email.
     */
    public function sendTest(Request $request, DripCampaign $campaign): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'step_id' => 'required|exists:drip_email_steps,id',
        ]);

        $step = DripEmailStep::findOrFail($validated['step_id']);

        if ($step->drip_campaign_id !== $campaign->id) {
            return back()->with('error', 'Invalid step for this campaign.');
        }

        try {
            Mail::to($validated['email'])->send(new DripEmail($step));

            Auth::guard('backoffice')->user()
                ->logActivity(
                    ActivityLog::ACTION_SEND_TEST_DRIP_EMAIL,
                    null,
                    'Sent test drip email to ' . $validated['email'] . ' for campaign: ' . $campaign->name
                );

            return back()->with('message', 'Test email sent successfully to ' . $validated['email']);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    /**
     * Add a new email step to a campaign.
     */
    public function addStep(Request $request, DripCampaign $campaign): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'delay_days' => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
        ]);

        $maxOrder = $campaign->steps()->max('sequence_order') ?? 0;
        $validated['sequence_order'] = $maxOrder + 1;

        $step = $campaign->steps()->create($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(
                ActivityLog::ACTION_UPDATE_DRIP_CAMPAIGN,
                null,
                'Added email step to campaign: ' . $campaign->name
            );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'step' => $step,
                'message' => 'Email step added successfully.',
            ]);
        }

        return back()->with('message', 'Email step added successfully.');
    }

    /**
     * Update an email step.
     */
    public function updateStep(Request $request, DripCampaign $campaign, DripEmailStep $step): RedirectResponse|JsonResponse
    {
        if ($step->drip_campaign_id !== $campaign->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid step for this campaign.'], 400);
            }
            return back()->with('error', 'Invalid step for this campaign.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'delay_days' => 'required|integer|min:0',
            'delay_hours' => 'required|integer|min:0|max:23',
        ]);

        $step->update($validated);

        Auth::guard('backoffice')->user()
            ->logActivity(
                ActivityLog::ACTION_UPDATE_DRIP_CAMPAIGN,
                null,
                'Updated email step in campaign: ' . $campaign->name
            );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'step' => $step->fresh(),
                'message' => 'Email step updated successfully.',
            ]);
        }

        return back()->with('message', 'Email step updated successfully.');
    }

    /**
     * Delete an email step.
     */
    public function deleteStep(Request $request, DripCampaign $campaign, DripEmailStep $step): RedirectResponse|JsonResponse
    {
        if ($step->drip_campaign_id !== $campaign->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid step for this campaign.'], 400);
            }
            return back()->with('error', 'Invalid step for this campaign.');
        }

        $step->delete();

        // Reorder remaining steps
        $campaign->steps()->orderBy('sequence_order')->get()->each(function ($s, $index) {
            $s->update(['sequence_order' => $index + 1]);
        });

        Auth::guard('backoffice')->user()
            ->logActivity(
                ActivityLog::ACTION_UPDATE_DRIP_CAMPAIGN,
                null,
                'Deleted email step from campaign: ' . $campaign->name
            );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Email step deleted successfully.',
            ]);
        }

        return back()->with('message', 'Email step deleted successfully.');
    }

    /**
     * Reorder email steps.
     */
    public function reorderSteps(Request $request, DripCampaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'steps' => 'required|array',
            'steps.*' => 'required|integer|exists:drip_email_steps,id',
        ]);

        foreach ($validated['steps'] as $index => $stepId) {
            DripEmailStep::where('id', $stepId)
                ->where('drip_campaign_id', $campaign->id)
                ->update(['sequence_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Steps reordered successfully.',
        ]);
    }
}
