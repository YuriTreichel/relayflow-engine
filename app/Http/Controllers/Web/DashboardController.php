<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExecutionLog;
use App\Models\Workflow;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'workflows' => Workflow::withCount('steps')->latest()->get(),
            'recentLogs' => ExecutionLog::with('workflow')
                ->latest()
                ->take(10)
                ->get(),
            'stats' => [
                'total_workflows' => Workflow::count(),
                'active_workflows' => Workflow::where('status', 'active')->count(),
                'completed_runs' => ExecutionLog::where('status', 'completed')->count(),
                'failed_runs' => ExecutionLog::where('status', 'failed')->count(),
            ],
        ]);
    }
}