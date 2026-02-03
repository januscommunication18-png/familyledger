<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Tenant;
use App\Models\PackagePlan;
use App\Mail\PaymentSuccessEmail;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): View
    {
        $query = Invoice::with(['tenant', 'user', 'packagePlan']);

        // Filter by tenant
        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by billing cycle
        if ($request->filled('billing_cycle')) {
            $query->where('billing_cycle', $request->billing_cycle);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by invoice number or customer email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $invoices = $query->latest()->paginate(20);
        $tenants = Tenant::orderBy('name')->get();

        // Calculate totals for stats
        $stats = [
            'total_revenue' => Invoice::paid()->sum('total_amount'),
            'this_month' => Invoice::paid()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total_amount'),
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::pending()->count(),
        ];

        return view('backoffice.invoices.index', compact('invoices', 'tenants', 'stats'));
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load(['tenant', 'user', 'packagePlan']);

        return view('backoffice.invoices.show', compact('invoice'));
    }

    /**
     * Resend invoice email.
     */
    public function resend(Invoice $invoice): RedirectResponse
    {
        $invoice->load(['tenant', 'user', 'packagePlan']);

        $user = $invoice->user ?? $invoice->tenant?->users()->first();

        if (!$user || !$user->email) {
            return back()->with('error', 'No valid email address found for this invoice.');
        }

        try {
            Mail::to($user->email)->send(new PaymentSuccessEmail($invoice, $user, $invoice->tenant));
            $invoice->markAsEmailed();

            Log::info('Invoice resent', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'email' => $user->email,
            ]);

            return back()->with('success', "Invoice #{$invoice->invoice_number} has been resent to {$user->email}");
        } catch (\Exception $e) {
            Log::error('Failed to resend invoice', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to resend invoice: ' . $e->getMessage());
        }
    }

    /**
     * Resend invoice to custom email.
     */
    public function resendToEmail(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $invoice->load(['tenant', 'user', 'packagePlan']);

        try {
            $user = $invoice->user ?? $invoice->tenant?->users()->first();

            Mail::to($request->email)->send(new PaymentSuccessEmail($invoice, $user, $invoice->tenant));
            $invoice->markAsEmailed();

            Log::info('Invoice resent to custom email', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'email' => $request->email,
            ]);

            return back()->with('success', "Invoice #{$invoice->invoice_number} has been sent to {$request->email}");
        } catch (\Exception $e) {
            Log::error('Failed to resend invoice to custom email', [
                'invoice_id' => $invoice->id,
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }

    /**
     * Add note to invoice.
     */
    public function addNote(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $invoice->update([
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Note added to invoice.');
    }

    /**
     * Export invoices to CSV.
     */
    public function export(Request $request)
    {
        $query = Invoice::with(['tenant', 'packagePlan']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->latest()->get();

        $filename = 'invoices_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Invoice Number',
                'Date',
                'Tenant',
                'Customer Email',
                'Plan',
                'Billing Cycle',
                'Subtotal',
                'Discount',
                'Tax',
                'Total',
                'Currency',
                'Status',
                'Paid At',
            ]);

            // Data rows
            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->created_at->format('Y-m-d'),
                    $invoice->tenant?->name ?? 'N/A',
                    $invoice->customer_email,
                    $invoice->packagePlan?->name ?? 'N/A',
                    $invoice->billing_cycle,
                    $invoice->subtotal,
                    $invoice->discount_amount,
                    $invoice->tax_amount,
                    $invoice->total_amount,
                    $invoice->currency,
                    $invoice->status,
                    $invoice->paid_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ============================================================
    // TESTING ONLY - Remove after testing is complete
    // ============================================================

    /**
     * Show form to create a test invoice.
     * TESTING ONLY - Remove after testing
     */
    public function createTest(): View
    {
        $tenants = Tenant::orderBy('name')->get();
        $plans = PackagePlan::active()->ordered()->get();

        return view('backoffice.invoices.create-test', compact('tenants', 'plans'));
    }

    /**
     * Store a test invoice.
     * TESTING ONLY - Remove after testing
     */
    public function storeTest(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'package_plan_id' => 'required|exists:package_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'total_amount' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'send_email' => 'boolean',
        ]);

        $tenant = Tenant::find($validated['tenant_id']);
        $plan = PackagePlan::find($validated['package_plan_id']);
        $user = $tenant->users()->first();

        // Create test invoice
        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user?->id,
            'package_plan_id' => $plan->id,
            'billing_cycle' => $validated['billing_cycle'],
            'subtotal' => $validated['total_amount'],
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'tax_amount' => 0,
            'total_amount' => $validated['total_amount'] - ($validated['discount_amount'] ?? 0),
            'currency' => 'USD',
            'status' => 'paid',
            'paid_at' => now(),
            'period_start' => now(),
            'period_end' => $validated['billing_cycle'] === 'yearly' ? now()->addYear() : now()->addMonth(),
            'customer_name' => $user?->name ?? $tenant->name,
            'customer_email' => $user?->email,
            'notes' => '[TEST INVOICE] Created manually for testing purposes',
        ]);

        // Optionally send email
        if ($request->boolean('send_email') && $user?->email) {
            try {
                Mail::to($user->email)->send(new PaymentSuccessEmail($invoice, $user, $tenant));
                $invoice->markAsEmailed();

                return redirect()->route('backoffice.invoices.show', $invoice)
                    ->with('success', "Test invoice #{$invoice->invoice_number} created and email sent to {$user->email}");
            } catch (\Exception $e) {
                Log::error('Failed to send test invoice email', [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);

                return redirect()->route('backoffice.invoices.show', $invoice)
                    ->with('warning', "Test invoice created but email failed: {$e->getMessage()}");
            }
        }

        return redirect()->route('backoffice.invoices.show', $invoice)
            ->with('success', "Test invoice #{$invoice->invoice_number} created successfully");
    }

    /**
     * Delete a test invoice.
     * TESTING ONLY - Remove after testing
     */
    public function destroyTest(Invoice $invoice): RedirectResponse
    {
        // Only allow deleting test invoices (those with test note)
        if (!str_contains($invoice->notes ?? '', '[TEST INVOICE]')) {
            return back()->with('error', 'Only test invoices can be deleted.');
        }

        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('backoffice.invoices.index')
            ->with('success', "Test invoice #{$invoiceNumber} deleted successfully");
    }

    // ============================================================
    // END TESTING ONLY
    // ============================================================
}
