<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Backoffice\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show dashboard.
     */
    public function index(): View
    {
        // Get stats
        $totalClients = Tenant::count();
        $activeClients = Tenant::where('is_active', true)->count();
        $inactiveClients = Tenant::where('is_active', false)->count();
        $totalUsers = User::count();

        // Get recent activity
        $recentActivity = ActivityLog::with('admin')
            ->latest()
            ->take(10)
            ->get();

        // Get recent clients
        $recentClients = Tenant::latest()
            ->take(5)
            ->get();

        // Monthly signups (last 6 months)
        $monthlySignups = Tenant::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return view('backoffice.dashboard', compact(
            'totalClients',
            'activeClients',
            'inactiveClients',
            'totalUsers',
            'recentActivity',
            'recentClients',
            'monthlySignups'
        ));
    }
}
