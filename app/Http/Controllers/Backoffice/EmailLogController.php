<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Backoffice\EmailLog;
use App\Models\Backoffice\DripEmailLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    /**
     * Display a listing of all email logs.
     */
    public function index(Request $request): View
    {
        // Get general email logs
        $query = EmailLog::query()->with(['user', 'tenant']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('mailable_type', $request->type);
        }

        // Filter by email
        if ($request->filled('email')) {
            $query->where('to_email', 'like', '%' . $request->email . '%');
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search in subject
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('to_email', 'like', "%{$search}%")
                    ->orWhere('mailable_type', 'like', "%{$search}%");
            });
        }

        $emailLogs = $query->latest()->paginate(30);

        // Get stats
        $stats = [
            'total' => EmailLog::count(),
            'sent' => EmailLog::where('status', 'sent')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'opened' => EmailLog::where('status', 'opened')->count(),
            'clicked' => EmailLog::where('status', 'clicked')->count(),
            'today' => EmailLog::whereDate('created_at', today())->count(),
        ];

        // Get unique types for filter dropdown
        $types = EmailLog::getUniqueTypes();

        return view('backoffice.email-logs.index', [
            'emailLogs' => $emailLogs,
            'stats' => $stats,
            'types' => $types,
            'statuses' => EmailLog::getStatuses(),
        ]);
    }

    /**
     * Display a specific email log.
     */
    public function show(EmailLog $emailLog): View
    {
        $emailLog->load(['user', 'tenant']);

        return view('backoffice.email-logs.show', [
            'emailLog' => $emailLog,
        ]);
    }

    /**
     * Display drip campaign email logs (consolidated view).
     */
    public function dripLogs(Request $request): View
    {
        $query = DripEmailLog::query()
            ->with(['campaign', 'step', 'user', 'tenant']);

        // Filter by campaign
        if ($request->filled('campaign')) {
            $query->where('drip_campaign_id', $request->campaign);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Date filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $dripLogs = $query->latest()->paginate(30);

        // Get stats
        $stats = [
            'total' => DripEmailLog::count(),
            'sent' => DripEmailLog::where('status', 'sent')->count(),
            'failed' => DripEmailLog::where('status', 'failed')->count(),
            'opened' => DripEmailLog::where('status', 'opened')->count(),
            'clicked' => DripEmailLog::where('status', 'clicked')->count(),
            'skipped' => DripEmailLog::where('status', 'skipped')->count(),
        ];

        // Get campaigns for filter
        $campaigns = \App\Models\Backoffice\DripCampaign::orderBy('name')->pluck('name', 'id');

        return view('backoffice.email-logs.drip', [
            'dripLogs' => $dripLogs,
            'stats' => $stats,
            'campaigns' => $campaigns,
            'statuses' => DripEmailLog::getStatuses(),
        ]);
    }
}
