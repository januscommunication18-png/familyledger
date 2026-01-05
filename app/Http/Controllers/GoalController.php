<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Goal;
use App\Models\GoalCheckIn;
use App\Models\GoalTemplate;
use App\Models\TodoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    /**
     * Display a listing of goals.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $query = Goal::where('tenant_id', $tenantId)
            ->with(['assignedTo', 'checkIns' => function ($q) {
                $q->latest('check_in_date')->limit(5);
            }]);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by assignment type
        if ($request->filled('assignment_type')) {
            $query->where('assignment_type', $request->assignment_type);
        }

        // Filter by family member
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter for kid goals only
        if ($request->boolean('kid_goals')) {
            $query->where('is_kid_goal', true);
        }

        // Filter for goals needing check-in
        if ($request->boolean('needs_check_in')) {
            $query->needsCheckIn();
        }

        $goals = $query
            ->orderByRaw("FIELD(status, 'active', 'in_progress', 'done', 'skipped', 'archived')")
            ->orderBy('created_at', 'desc')
            ->get();

        // Get goals needing check-in for badge
        $goalsNeedingCheckIn = Goal::where('tenant_id', $tenantId)
            ->needsCheckIn()
            ->count();

        // Get family members for filter dropdown
        $familyMembers = FamilyMember::where('tenant_id', $tenantId)->get();

        return view('pages.goals-todo.goals-index', [
            'goals' => $goals,
            'goalsNeedingCheckIn' => $goalsNeedingCheckIn,
            'familyMembers' => $familyMembers,
            'categories' => Goal::CATEGORIES,
            'assignmentTypes' => Goal::ASSIGNMENT_TYPES,
            'statuses' => Goal::STATUSES,
            'filters' => $request->only(['category', 'assignment_type', 'assigned_to', 'status', 'kid_goals', 'needs_check_in']),
        ]);
    }

    /**
     * Show the form for creating a new goal.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get family members for assignment dropdown
        $familyMembers = FamilyMember::where('tenant_id', $tenantId)->get();

        // Get available templates
        $templates = GoalTemplate::availableTo($tenantId)
            ->active()
            ->orderBy('audience')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('audience');

        // If creating from a template
        $fromTemplate = null;
        if ($request->filled('template_id')) {
            $fromTemplate = GoalTemplate::find($request->template_id);
        }

        return view('pages.goals-todo.goal-form', [
            'goal' => null,
            'categories' => Goal::CATEGORIES,
            'assignmentTypes' => Goal::ASSIGNMENT_TYPES,
            'goalTypes' => Goal::GOAL_TYPES,
            'habitFrequencies' => Goal::HABIT_FREQUENCIES,
            'rewardTypes' => Goal::REWARD_TYPES,
            'checkInFrequencies' => Goal::CHECK_IN_FREQUENCIES,
            'familyMembers' => $familyMembers,
            'templates' => $templates,
            'fromTemplate' => $fromTemplate,
            // Legacy support
            'colors' => Goal::COLORS,
            'icons' => Goal::ICONS,
            'targetTypes' => Goal::TARGET_TYPES,
        ]);
    }

    /**
     * Store a newly created goal.
     */
    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:' . implode(',', array_keys(Goal::CATEGORIES)),
            'assignment_type' => 'required|string|in:' . implode(',', array_keys(Goal::ASSIGNMENT_TYPES)),
            'assigned_to' => 'nullable|exists:family_members,id',
            'goal_type' => 'required|string|in:' . implode(',', array_keys(Goal::GOAL_TYPES)),
            'habit_frequency' => 'nullable|string|in:' . implode(',', array_keys(Goal::HABIT_FREQUENCIES)),
            'milestone_target' => 'nullable|integer|min:1',
            'milestone_unit' => 'nullable|string|max:50',
            'is_kid_goal' => 'nullable|boolean',
            'rewards_enabled' => 'nullable|boolean',
            'reward_type' => 'nullable|string|in:' . implode(',', array_keys(Goal::REWARD_TYPES)),
            'reward_custom' => 'nullable|string|max:255',
            'check_in_frequency' => 'nullable|string|in:' . implode(',', array_keys(Goal::CHECK_IN_FREQUENCIES)),
            'visible_to_kids' => 'nullable|boolean',
            'kids_can_update' => 'nullable|boolean',
            'template_id' => 'nullable|exists:goal_templates,id',
        ];

        // Conditional validation
        if ($request->goal_type === 'habit') {
            $rules['habit_frequency'] = 'required|string|in:' . implode(',', array_keys(Goal::HABIT_FREQUENCIES));
        }
        if ($request->goal_type === 'milestone') {
            $rules['milestone_target'] = 'required|integer|min:1';
        }
        if ($request->boolean('rewards_enabled')) {
            $rules['reward_type'] = 'required|string|in:' . implode(',', array_keys(Goal::REWARD_TYPES));
        }

        $request->validate($rules);

        $user = Auth::user();

        $goal = Goal::create([
            'tenant_id' => $user->tenant_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'assignment_type' => $request->assignment_type,
            'assigned_to' => $request->assignment_type === 'individual' ? $request->assigned_to : null,
            'goal_type' => $request->goal_type,
            'habit_frequency' => $request->goal_type === 'habit' ? $request->habit_frequency : null,
            'milestone_target' => $request->goal_type === 'milestone' ? $request->milestone_target : null,
            'milestone_current' => 0,
            'milestone_unit' => $request->goal_type === 'milestone' ? $request->milestone_unit : null,
            'is_kid_goal' => $request->boolean('is_kid_goal'),
            'show_emoji_status' => true,
            'rewards_enabled' => $request->boolean('rewards_enabled'),
            'reward_type' => $request->boolean('rewards_enabled') ? $request->reward_type : null,
            'reward_custom' => $request->reward_type === 'custom' ? $request->reward_custom : null,
            'check_in_frequency' => $request->check_in_frequency,
            'next_check_in' => $request->check_in_frequency ? now() : null,
            'visible_to_kids' => $request->boolean('visible_to_kids', true),
            'kids_can_update' => $request->boolean('kids_can_update'),
            'template_id' => $request->template_id,
            'status' => 'active',
            'created_by' => $user->id,
            // Legacy support
            'color' => Goal::CATEGORIES[$request->category]['color'] ?? 'violet',
            'icon' => 'target',
            'target_type' => 'none',
        ]);

        return redirect()->route('goals-todo.index', ['tab' => 'goals'])
            ->with('success', 'Goal created successfully!');
    }

    /**
     * Display the specified goal with check-ins.
     */
    public function show(Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $goal->load([
            'assignedTo',
            'template',
            'checkIns' => function ($q) {
                $q->with('checkedInBy')->latest('check_in_date')->limit(20);
            },
            'tasks' => function ($query) {
                $query->whereNull('parent_task_id')
                    ->with(['assignees'])
                    ->orderByRaw("FIELD(status, 'open', 'in_progress', 'snoozed', 'completed', 'skipped')")
                    ->orderBy('due_date', 'asc');
            },
            'createdBy',
        ]);

        // Get check-in stats for this week
        $weeklyCheckIns = $goal->checkIns()
            ->thisWeek()
            ->completed()
            ->count();

        return view('pages.goals-todo.goal-detail', [
            'goal' => $goal,
            'weeklyCheckIns' => $weeklyCheckIns,
        ]);
    }

    /**
     * Show the form for editing the specified goal.
     */
    public function edit(Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        // Get family members for assignment dropdown
        $familyMembers = FamilyMember::where('tenant_id', $user->tenant_id)->get();

        return view('pages.goals-todo.goal-form', [
            'goal' => $goal,
            'categories' => Goal::CATEGORIES,
            'assignmentTypes' => Goal::ASSIGNMENT_TYPES,
            'goalTypes' => Goal::GOAL_TYPES,
            'habitFrequencies' => Goal::HABIT_FREQUENCIES,
            'rewardTypes' => Goal::REWARD_TYPES,
            'checkInFrequencies' => Goal::CHECK_IN_FREQUENCIES,
            'familyMembers' => $familyMembers,
            'templates' => collect(),
            'fromTemplate' => null,
            // Legacy support
            'colors' => Goal::COLORS,
            'icons' => Goal::ICONS,
            'targetTypes' => Goal::TARGET_TYPES,
        ]);
    }

    /**
     * Update the specified goal.
     */
    public function update(Request $request, Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|in:' . implode(',', array_keys(Goal::CATEGORIES)),
            'assignment_type' => 'required|string|in:' . implode(',', array_keys(Goal::ASSIGNMENT_TYPES)),
            'assigned_to' => 'nullable|exists:family_members,id',
            'goal_type' => 'required|string|in:' . implode(',', array_keys(Goal::GOAL_TYPES)),
            'habit_frequency' => 'nullable|string|in:' . implode(',', array_keys(Goal::HABIT_FREQUENCIES)),
            'milestone_target' => 'nullable|integer|min:1',
            'milestone_unit' => 'nullable|string|max:50',
            'is_kid_goal' => 'nullable|boolean',
            'rewards_enabled' => 'nullable|boolean',
            'reward_type' => 'nullable|string|in:' . implode(',', array_keys(Goal::REWARD_TYPES)),
            'reward_custom' => 'nullable|string|max:255',
            'check_in_frequency' => 'nullable|string|in:' . implode(',', array_keys(Goal::CHECK_IN_FREQUENCIES)),
            'visible_to_kids' => 'nullable|boolean',
            'kids_can_update' => 'nullable|boolean',
            'status' => 'nullable|string|in:' . implode(',', array_keys(Goal::STATUSES)),
        ];

        // Conditional validation
        if ($request->goal_type === 'habit') {
            $rules['habit_frequency'] = 'required|string|in:' . implode(',', array_keys(Goal::HABIT_FREQUENCIES));
        }
        if ($request->goal_type === 'milestone') {
            $rules['milestone_target'] = 'required|integer|min:1';
        }
        if ($request->boolean('rewards_enabled')) {
            $rules['reward_type'] = 'required|string|in:' . implode(',', array_keys(Goal::REWARD_TYPES));
        }

        $request->validate($rules);

        $goal->update([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'assignment_type' => $request->assignment_type,
            'assigned_to' => $request->assignment_type === 'individual' ? $request->assigned_to : null,
            'goal_type' => $request->goal_type,
            'habit_frequency' => $request->goal_type === 'habit' ? $request->habit_frequency : null,
            'milestone_target' => $request->goal_type === 'milestone' ? $request->milestone_target : $goal->milestone_target,
            'milestone_unit' => $request->goal_type === 'milestone' ? $request->milestone_unit : $goal->milestone_unit,
            'is_kid_goal' => $request->boolean('is_kid_goal'),
            'rewards_enabled' => $request->boolean('rewards_enabled'),
            'reward_type' => $request->boolean('rewards_enabled') ? $request->reward_type : null,
            'reward_custom' => $request->reward_type === 'custom' ? $request->reward_custom : null,
            'check_in_frequency' => $request->check_in_frequency,
            'visible_to_kids' => $request->boolean('visible_to_kids', true),
            'kids_can_update' => $request->boolean('kids_can_update'),
            'status' => $request->status ?? $goal->status,
            // Legacy support
            'color' => Goal::CATEGORIES[$request->category]['color'] ?? $goal->color,
        ]);

        return redirect()->route('goals-todo.goals.show', $goal)
            ->with('success', 'Goal updated successfully!');
    }

    /**
     * Remove the specified goal.
     */
    public function destroy(Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        // Unlink tasks from this goal (don't delete them)
        $goal->tasks()->update(['goal_id' => null, 'count_toward_goal' => false]);

        // Check-ins will be cascade deleted via foreign key

        $goal->delete();

        return redirect()->route('goals-todo.index', ['tab' => 'goals'])
            ->with('success', 'Goal deleted successfully.');
    }

    /**
     * Record a check-in for a goal.
     */
    public function checkIn(Request $request, Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|string|in:done,in_progress,skipped',
            'note' => 'nullable|string|max:500',
            'progress_added' => 'nullable|integer|min:0',
            'star_rating' => 'nullable|integer|min:1|max:3',
            'parent_message' => 'nullable|string|max:500',
        ]);

        $checkIn = $goal->recordCheckIn(
            $request->status,
            $request->progress_added,
            $request->note
        );

        // Update additional fields
        if ($request->filled('star_rating')) {
            $checkIn->update(['star_rating' => $request->star_rating]);
            // Update goal's star rating too
            $goal->update(['star_rating' => $request->star_rating]);
        }

        if ($request->filled('parent_message')) {
            $checkIn->update(['parent_message' => $request->parent_message]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'check_in' => $checkIn,
                'goal_status' => $goal->fresh()->status,
                'next_check_in' => $goal->next_check_in?->format('M j, Y'),
            ]);
        }

        return redirect()->back()->with('success', 'Check-in recorded!');
    }

    /**
     * Mark goal as done.
     */
    public function markDone(Request $request, Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $goal->markDone();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $goal->status,
                'status_emoji' => $goal->status_emoji,
            ]);
        }

        return redirect()->back()->with('success', 'Goal marked as done!');
    }

    /**
     * Skip the goal.
     */
    public function skip(Request $request, Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $goal->skip();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $goal->status,
            ]);
        }

        return redirect()->back()->with('success', 'Goal skipped.');
    }

    /**
     * Claim the reward for a completed goal.
     */
    public function claimReward(Request $request, Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $success = $goal->claimReward();

        if ($request->ajax()) {
            return response()->json([
                'success' => $success,
                'reward_claimed' => $goal->reward_claimed,
            ]);
        }

        if ($success) {
            return redirect()->back()->with('success', 'Reward claimed!');
        }

        return redirect()->back()->with('error', 'Cannot claim reward yet.');
    }

    /**
     * Update milestone progress.
     */
    public function updateProgress(Request $request, Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'progress' => 'required|numeric|min:0',
        ]);

        if ($goal->goal_type === 'milestone') {
            $goal->update(['milestone_current' => $request->progress]);
            $goal->checkCompletion();
        } else {
            // Legacy support
            $goal->update(['current_progress' => $request->progress]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'progress' => $goal->milestone_current ?? $goal->current_progress,
                'percentage' => $goal->milestone_progress ?? $goal->progress_percentage,
                'status' => $goal->status,
                'star_display' => $goal->star_display,
            ]);
        }

        return redirect()->back()->with('success', 'Progress updated.');
    }

    /**
     * Toggle goal status (pause/resume/archive).
     */
    public function toggleStatus(Request $request, Goal $goal)
    {
        $user = Auth::user();

        if ($goal->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(Goal::STATUSES)),
        ]);

        $goal->update(['status' => $request->status]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $goal->status,
            ]);
        }

        return redirect()->back()->with('success', 'Goal status updated.');
    }

    /**
     * Get goal templates (for AJAX).
     */
    public function templates(Request $request)
    {
        $user = Auth::user();

        $templates = GoalTemplate::availableTo($user->tenant_id)
            ->active()
            ->when($request->filled('audience'), function ($q) use ($request) {
                $q->forAudience($request->audience);
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->forCategory($request->category);
            })
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Create goal from template.
     */
    public function createFromTemplate(Request $request, GoalTemplate $template)
    {
        $user = Auth::user();

        $request->validate([
            'assigned_to' => 'nullable|exists:family_members,id',
        ]);

        $goal = $template->createGoal($user->tenant_id, [
            'assigned_to' => $request->assigned_to,
            'created_by' => $user->id,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'goal' => $goal,
                'redirect' => route('goals-todo.goals.show', $goal),
            ]);
        }

        return redirect()->route('goals-todo.goals.show', $goal)
            ->with('success', 'Goal created from template!');
    }
}
