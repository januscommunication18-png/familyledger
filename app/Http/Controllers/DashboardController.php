<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\BudgetTransaction;
use App\Models\FamilyCircle;
use App\Models\FamilyMember;
use App\Models\FamilyResource;
use App\Models\Goal;
use App\Models\InsurancePolicy;
use App\Models\JournalEntry;
use App\Models\LegalDocument;
use App\Models\MemberDocument;
use App\Models\Pet;
use App\Models\Person;
use App\Models\ShoppingList;
use App\Models\TaxReturn;
use App\Models\TodoItem;
use App\Models\CollaboratorInvite;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Subscription expiration status
        $tenant = Tenant::with('packagePlan')->find($tenantId);
        $subscriptionAlert = $this->getSubscriptionAlert($tenant);

        // Pending co-parent invites
        $pendingCoparentInvites = CollaboratorInvite::where('email', $user->email)
            ->coparentInvites()
            ->pending()
            ->with(['inviter', 'familyMembers'])
            ->get();

        // Family Statistics
        $familyCircles = FamilyCircle::where('tenant_id', $tenantId)->get();
        $familyMembers = FamilyMember::where('tenant_id', $tenantId)->get();
        $membersByRelationship = $familyMembers->groupBy('relationship')->map->count();

        // Documents count
        $legalDocuments = LegalDocument::where('tenant_id', $tenantId)->count();
        $familyResources = FamilyResource::where('tenant_id', $tenantId)->count();
        $memberDocuments = MemberDocument::whereIn('family_member_id', $familyMembers->pluck('id'))->count();
        $totalDocuments = $legalDocuments + $familyResources + $memberDocuments;

        // Assets
        $assets = Asset::where('tenant_id', $tenantId)->get();
        $totalAssetValue = $assets->sum('current_value');
        $assetsByCategory = $assets->groupBy('asset_category')->map(function ($group) {
            return [
                'count' => $group->count(),
                'value' => $group->sum('current_value'),
            ];
        });

        // Tasks/Todos
        $todos = TodoItem::where('tenant_id', $tenantId)->get();
        $pendingTasks = $todos->whereIn('status', ['open', 'in_progress'])->count();
        $completedTasks = $todos->where('status', 'completed')->count();
        $overdueTasks = $todos->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->count();

        // Upcoming tasks (next 7 days)
        $upcomingTasks = TodoItem::where('tenant_id', $tenantId)
            ->whereIn('status', ['open', 'in_progress'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Budget/Expenses (last 6 months)
        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();
        $expensesByMonth = BudgetTransaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $incomeByMonth = BudgetTransaction::where('tenant_id', $tenantId)
            ->where('type', 'income')
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->selectRaw('YEAR(transaction_date) as year, MONTH(transaction_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Monthly expense breakdown by category (current month)
        $currentMonthExpenses = BudgetTransaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->with('category')
            ->get()
            ->groupBy(fn($t) => $t->category->name ?? 'Uncategorized')
            ->map->sum('amount');

        $thisMonthTotal = $currentMonthExpenses->sum();
        $lastMonthTotal = BudgetTransaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->subMonth()->year)
            ->sum('amount');

        // Goals
        $goals = Goal::where('tenant_id', $tenantId)->get();
        $activeGoals = $goals->where('status', 'active')->count();
        $completedGoals = $goals->where('status', 'completed')->count();

        // Insurance policies
        $insurancePolicies = InsurancePolicy::where('tenant_id', $tenantId)->get();
        $activeInsurance = $insurancePolicies->where('status', 'active')->count();
        $expiringInsurance = $insurancePolicies->where('status', 'active')
            ->filter(fn($p) => $p->expiration_date && $p->expiration_date->isBetween(now(), now()->addDays(30)))
            ->count();

        // Pets
        $pets = Pet::where('tenant_id', $tenantId)->count();

        // People/Contacts
        $contacts = Person::where('tenant_id', $tenantId)->count();

        // Shopping Lists - count unchecked items across all lists
        $shoppingLists = ShoppingList::where('tenant_id', $tenantId)
            ->withCount(['items as pending_items' => fn($q) => $q->where('is_checked', false)])
            ->get();
        $pendingShoppingItems = $shoppingLists->sum('pending_items');

        // Journal entries (recent)
        $recentJournalEntries = JournalEntry::where('tenant_id', $tenantId)
            ->orderBy('entry_datetime', 'desc')
            ->limit(5)
            ->get();

        // Upcoming birthdays (from family members)
        $upcomingBirthdays = $familyMembers
            ->filter(fn($m) => $m->date_of_birth)
            ->map(function ($member) {
                $birthday = Carbon::parse($member->date_of_birth);
                $nextBirthday = $birthday->copy()->year(now()->year)->startOfDay();
                if ($nextBirthday->isPast()) {
                    $nextBirthday->addYear();
                }
                $member->next_birthday = $nextBirthday;
                $member->days_until = (int) now()->startOfDay()->diffInDays($nextBirthday, false);
                return $member;
            })
            ->filter(fn($m) => $m->days_until >= 0 && $m->days_until <= 30)
            ->sortBy('days_until')
            ->take(5);

        // Expiring documents (passports, licenses, etc.)
        $expiringDocuments = MemberDocument::whereIn('family_member_id', $familyMembers->pluck('id'))
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addMonths(3)])
            ->with('familyMember')
            ->orderBy('expiry_date')
            ->limit(5)
            ->get();

        // Chart data preparation
        $chartLabels = [];
        $expenseData = [];
        $incomeData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $chartLabels[] = $date->format('M Y');
            $expenseData[] = $expensesByMonth->first(fn($e) => $e->year == $date->year && $e->month == $date->month)?->total ?? 0;
            $incomeData[] = $incomeByMonth->first(fn($e) => $e->year == $date->year && $e->month == $date->month)?->total ?? 0;
        }

        // Asset allocation chart data
        $assetChartLabels = [];
        $assetChartData = [];
        $assetChartColors = [
            'property' => '#6366f1',
            'vehicle' => '#22c55e',
            'valuable' => '#f59e0b',
            'inventory' => '#ec4899',
        ];
        foreach ($assetsByCategory as $category => $data) {
            $assetChartLabels[] = ucfirst($category);
            $assetChartData[] = $data['value'];
        }

        // Expense categories chart
        $expenseCategoryLabels = $currentMonthExpenses->keys()->toArray();
        $expenseCategoryData = $currentMonthExpenses->values()->toArray();

        return view('dashboard', compact(
            'user',
            'pendingCoparentInvites',
            'familyCircles',
            'familyMembers',
            'membersByRelationship',
            'totalDocuments',
            'legalDocuments',
            'familyResources',
            'assets',
            'totalAssetValue',
            'assetsByCategory',
            'pendingTasks',
            'completedTasks',
            'overdueTasks',
            'upcomingTasks',
            'thisMonthTotal',
            'lastMonthTotal',
            'currentMonthExpenses',
            'goals',
            'activeGoals',
            'completedGoals',
            'insurancePolicies',
            'activeInsurance',
            'expiringInsurance',
            'pets',
            'contacts',
            'shoppingLists',
            'pendingShoppingItems',
            'recentJournalEntries',
            'upcomingBirthdays',
            'expiringDocuments',
            'chartLabels',
            'expenseData',
            'incomeData',
            'assetChartLabels',
            'assetChartData',
            'assetChartColors',
            'expenseCategoryLabels',
            'expenseCategoryData',
            'todos',
            'subscriptionAlert'
        ));
    }

    /**
     * Get subscription alert data for display.
     */
    private function getSubscriptionAlert(?Tenant $tenant): ?array
    {
        if (!$tenant || !$tenant->packagePlan) {
            return null;
        }

        // Only show for paid plans
        if ($tenant->packagePlan->type !== 'paid') {
            return null;
        }

        $expiresAt = $tenant->subscription_expires_at;

        if (!$expiresAt) {
            return null;
        }

        $now = now();
        $daysRemaining = (int) $now->diffInDays($expiresAt, false);

        // Subscription already expired
        if ($daysRemaining < 0) {
            $daysExpired = abs($daysRemaining);
            return [
                'type' => 'expired',
                'severity' => 'error',
                'title' => 'Your subscription has expired',
                'message' => $daysExpired === 1
                    ? 'Your ' . $tenant->packagePlan->name . ' plan expired yesterday. Renew now to continue enjoying premium features.'
                    : 'Your ' . $tenant->packagePlan->name . ' plan expired ' . $daysExpired . ' days ago. Renew now to continue enjoying premium features.',
                'cta' => 'Renew Subscription',
                'daysExpired' => $daysExpired,
                'planName' => $tenant->packagePlan->name,
                'dismissKey' => 'sub_expired_' . $tenant->id . '_' . $expiresAt->format('Y-m-d'),
            ];
        }

        // Expiring soon (within 7 days)
        if ($daysRemaining <= 7) {
            return [
                'type' => 'expiring_soon',
                'severity' => $daysRemaining <= 3 ? 'warning' : 'info',
                'title' => $daysRemaining === 0
                    ? 'Your subscription expires today!'
                    : ($daysRemaining === 1
                        ? 'Your subscription expires tomorrow!'
                        : 'Your subscription expires in ' . $daysRemaining . ' days'),
                'message' => 'Your ' . $tenant->packagePlan->name . ' plan will expire on ' . $expiresAt->format('M j, Y') . '. Renew now to avoid service interruption.',
                'cta' => 'Renew Now',
                'daysRemaining' => $daysRemaining,
                'expiresAt' => $expiresAt->format('M j, Y'),
                'planName' => $tenant->packagePlan->name,
                'dismissKey' => 'sub_expiring_' . $tenant->id . '_' . $expiresAt->format('Y-m-d'),
            ];
        }

        return null;
    }
}
