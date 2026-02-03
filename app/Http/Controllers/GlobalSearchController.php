<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\FamilyCircle;
use App\Models\Person;
use App\Models\Pet;
use App\Models\Goal;
use App\Models\TodoItem;
use App\Models\LegalDocument;
use App\Models\Asset;
use App\Models\FamilyResource;
use App\Models\JournalEntry;
use App\Models\InsurancePolicy;
use App\Models\TaxReturn;
use App\Models\ShoppingList;
use App\Models\TodoList;
use App\Models\BudgetTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GlobalSearchController extends Controller
{
    /**
     * Search across all entities by name.
     */
    public function search(Request $request)
    {
        try {
            $query = trim($request->input('q', ''));

            if (strlen($query) < 2) {
                return response()->json(['results' => []]);
            }

            $tenantId = Auth::user()->tenant_id;
            $results = [];
            $limit = 5; // Max results per category

            // Search Family Members - first_name/last_name are encrypted, so filter in PHP
            $members = FamilyMember::where('tenant_id', $tenantId)
                ->limit(100) // Fetch more to filter in PHP
                ->get()
                ->filter(function ($member) use ($query) {
                    $fullName = $member->first_name . ' ' . $member->last_name;
                    return stripos($member->first_name ?? '', $query) !== false ||
                           stripos($member->last_name ?? '', $query) !== false ||
                           stripos($fullName, $query) !== false;
                })
                ->take($limit);

            foreach ($members as $member) {
                $results[] = [
                    'type' => 'member',
                    'category' => 'Family Members',
                    'icon' => 'user',
                    'title' => $member->full_name,
                    'subtitle' => $member->relationship ? ucfirst($member->relationship) : 'Family Member',
                    'url' => route('family-circle.member.show', [$member->family_circle_id, $member->id]),
                ];
            }

        // Search Family Circles
        $circles = FamilyCircle::where('tenant_id', $tenantId)
            ->where('name', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($circles as $circle) {
            $results[] = [
                'type' => 'circle',
                'category' => 'Family Circles',
                'icon' => 'users',
                'title' => $circle->name,
                'subtitle' => $circle->members_count . ' members',
                'url' => route('family-circle.show', $circle->id),
            ];
        }

        // Search People (Contacts) - full_name is encrypted, so we fetch and filter in PHP
        $people = Person::where('tenant_id', $tenantId)
            ->limit(50) // Fetch more to filter
            ->get()
            ->filter(function ($person) use ($query) {
                return stripos($person->full_name, $query) !== false ||
                       stripos($person->nickname ?? '', $query) !== false;
            })
            ->take($limit);

        foreach ($people as $person) {
            $results[] = [
                'type' => 'person',
                'category' => 'People',
                'icon' => 'address-book',
                'title' => $person->full_name,
                'subtitle' => $person->category ? ucfirst($person->category) : 'Contact',
                'url' => route('people.show', $person->id),
            ];
        }

        // Search Pets
        $pets = Pet::where('tenant_id', $tenantId)
            ->where('name', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($pets as $pet) {
            $results[] = [
                'type' => 'pet',
                'category' => 'Pets',
                'icon' => 'paw',
                'title' => $pet->name,
                'subtitle' => $pet->species ? ucfirst($pet->species) : 'Pet',
                'url' => route('pets.show', $pet->id),
            ];
        }

        // Search Goals
        $goals = Goal::where('tenant_id', $tenantId)
            ->where('title', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($goals as $goal) {
            $results[] = [
                'type' => 'goal',
                'category' => 'Goals',
                'icon' => 'target',
                'title' => $goal->title,
                'subtitle' => ucfirst($goal->status ?? 'active'),
                'url' => route('goals-todo.goals.show', $goal->id),
            ];
        }

        // Search Todo Items (Tasks)
        $todos = TodoItem::where('tenant_id', $tenantId)
            ->where('title', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($todos as $todo) {
            $results[] = [
                'type' => 'todo',
                'category' => 'Tasks',
                'icon' => 'checkbox',
                'title' => $todo->title,
                'subtitle' => ucfirst($todo->status ?? 'pending'),
                'url' => route('goals-todo.tasks.edit', $todo->id),
            ];
        }

        // Search Legal Documents - name is encrypted, so filter in PHP
        $legalDocs = LegalDocument::where('tenant_id', $tenantId)
            ->limit(50)
            ->get()
            ->filter(function ($doc) use ($query) {
                return stripos($doc->name ?? '', $query) !== false ||
                       stripos($doc->document_type ?? '', $query) !== false;
            })
            ->take($limit);

        foreach ($legalDocs as $doc) {
            $results[] = [
                'type' => 'legal',
                'category' => 'Legal Documents',
                'icon' => 'scale',
                'title' => $doc->name,
                'subtitle' => $doc->document_type ? ucfirst(str_replace('_', ' ', $doc->document_type)) : 'Legal Document',
                'url' => route('legal.show', $doc->id),
            ];
        }

        // Search Assets
        $assets = Asset::where('tenant_id', $tenantId)
            ->where('name', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($assets as $asset) {
            $results[] = [
                'type' => 'asset',
                'category' => 'Assets',
                'icon' => 'building-bank',
                'title' => $asset->name,
                'subtitle' => $asset->asset_type ? ucfirst(str_replace('_', ' ', $asset->asset_type)) : 'Asset',
                'url' => route('assets.show', $asset->id),
            ];
        }

        // Search Family Resources - name is encrypted, so filter in PHP
        $resources = FamilyResource::where('tenant_id', $tenantId)
            ->limit(50)
            ->get()
            ->filter(function ($resource) use ($query) {
                return stripos($resource->name ?? '', $query) !== false ||
                       stripos($resource->document_type ?? '', $query) !== false ||
                       stripos($resource->custom_document_type ?? '', $query) !== false;
            })
            ->take($limit);

        foreach ($resources as $resource) {
            $results[] = [
                'type' => 'resource',
                'category' => 'Resources',
                'icon' => 'folder',
                'title' => $resource->name,
                'subtitle' => $resource->document_type ? ucfirst(str_replace('_', ' ', $resource->document_type)) : 'Resource',
                'url' => route('family-resources.show', $resource->id),
            ];
        }

        // Search Journal Entries
        $journals = JournalEntry::where('tenant_id', $tenantId)
            ->where('title', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($journals as $journal) {
            $results[] = [
                'type' => 'journal',
                'category' => 'Journal',
                'icon' => 'notebook',
                'title' => $journal->title,
                'subtitle' => $journal->created_at ? $journal->created_at->format('M d, Y') : 'Journal Entry',
                'url' => route('journal.show', $journal->id),
            ];
        }

        // Search Insurance Policies - provider_name/plan_name are encrypted, so filter in PHP
        $insurances = InsurancePolicy::where('tenant_id', $tenantId)
            ->limit(50)
            ->get()
            ->filter(function ($insurance) use ($query) {
                return stripos($insurance->provider_name ?? '', $query) !== false ||
                       stripos($insurance->plan_name ?? '', $query) !== false ||
                       stripos($insurance->insurance_type ?? '', $query) !== false;
            })
            ->take($limit);

        foreach ($insurances as $insurance) {
            $results[] = [
                'type' => 'insurance',
                'category' => 'Insurance',
                'icon' => 'shield',
                'title' => $insurance->provider_name . ($insurance->plan_name ? ' - ' . $insurance->plan_name : ''),
                'subtitle' => $insurance->insurance_type ? ucfirst(str_replace('_', ' ', $insurance->insurance_type)) : 'Insurance Policy',
                'url' => route('documents.insurance.show', $insurance->id),
            ];
        }

        // Search Tax Returns - cpa_name is encrypted, so filter in PHP
        $taxReturns = TaxReturn::where('tenant_id', $tenantId)
            ->limit(50)
            ->get()
            ->filter(function ($taxReturn) use ($query) {
                return stripos((string)$taxReturn->tax_year, $query) !== false ||
                       stripos($taxReturn->filing_status ?? '', $query) !== false ||
                       stripos($taxReturn->cpa_name ?? '', $query) !== false ||
                       stripos('tax return', $query) !== false;
            })
            ->take($limit);

        foreach ($taxReturns as $taxReturn) {
            $results[] = [
                'type' => 'tax',
                'category' => 'Tax Returns',
                'icon' => 'receipt',
                'title' => 'Tax Return ' . $taxReturn->tax_year,
                'subtitle' => $taxReturn->filing_status ? ucfirst(str_replace('_', ' ', $taxReturn->filing_status)) : 'Tax Return',
                'url' => route('documents.tax-returns.show', $taxReturn->id),
            ];
        }

        // Search Shopping Lists
        $shoppingLists = ShoppingList::where('tenant_id', $tenantId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('store', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        foreach ($shoppingLists as $list) {
            $results[] = [
                'type' => 'shopping',
                'category' => 'Shopping Lists',
                'icon' => 'shopping-cart',
                'title' => $list->name,
                'subtitle' => $list->store ?: 'Shopping List',
                'url' => route('shopping.show', $list->id),
            ];
        }

        // Search Todo Lists
        $todoLists = TodoList::where('tenant_id', $tenantId)
            ->where('name', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($todoLists as $list) {
            $results[] = [
                'type' => 'todolist',
                'category' => 'Todo Lists',
                'icon' => 'list',
                'title' => $list->name,
                'subtitle' => 'Todo List',
                'url' => route('goals-todo.index', ['tab' => 'todos', 'list' => $list->id]),
            ];
        }

        // Search Expense Transactions
        $transactions = BudgetTransaction::where('tenant_id', $tenantId)
            ->where(function ($q) use ($query) {
                $q->where('description', 'LIKE', "%{$query}%")
                  ->orWhere('payee', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        foreach ($transactions as $tx) {
            $results[] = [
                'type' => 'expense',
                'category' => 'Expenses',
                'icon' => 'wallet',
                'title' => $tx->description ?: $tx->payee ?: 'Transaction',
                'subtitle' => ($tx->type === 'income' ? '+' : '-') . '$' . number_format($tx->amount, 2),
                'url' => route('expenses.transactions.show', $tx->id),
            ];
        }

            // Sort by relevance (exact matches first)
            usort($results, function ($a, $b) use ($query) {
                $aExact = stripos($a['title'], $query) === 0 ? 0 : 1;
                $bExact = stripos($b['title'], $query) === 0 ? 0 : 1;
                return $aExact - $bExact;
            });

            // Limit total results
            $results = array_slice($results, 0, 20);

            return response()->json([
                'results' => $results,
                'query' => $query,
            ]);
        } catch (\Exception $e) {
            Log::error('Global search error: ' . $e->getMessage(), [
                'query' => $request->input('q'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'results' => [],
                'query' => $request->input('q'),
                'error' => config('app.debug') ? $e->getMessage() : 'Search error occurred'
            ], 200);
        }
    }
}
