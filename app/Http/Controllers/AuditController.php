<?php

namespace App\Http\Controllers;

use App\Models\AuditActivity;
use App\Models\User;
use Illuminate\Http\Request;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display audit trail.
     */
    public function index(Request $request)
    {
        $query = AuditActivity::with('user');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('module_name', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%");
            });
        }

        $activities = $query->orderBy('performed_at', 'desc')
            ->paginate($request->get('per_page', 25))
            ->withQueryString();

        $stats = $this->getStatistics($request);
        $actions = AuditActivity::distinct()->pluck('action');
        $modules = AuditActivity::distinct()->pluck('module');
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.audit.index', compact('activities', 'stats', 'actions', 'modules', 'users'));
    }

    /**
     * Show audit detail.
     */
    public function show(AuditActivity $activity)
    {
        $activity->load('user');
        return view('admin.audit.show', compact('activity'));
    }

    /**
     * Export audit trail.
     */
    public function export(Request $request)
    {
        $query = AuditActivity::query();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('performed_at', 'desc')->get();

        $filename = 'audit-trail-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $columns = ['Date/Time', 'User', 'Role', 'Action', 'Module', 'Module Name',
                    'Description', 'IP Address', 'Status'];

        $callback = function() use ($activities, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->performed_at->format('Y-m-d H:i:s'),
                    $activity->user_name ?? 'System',
                    $activity->user_role ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $activity->action)),
                    $activity->module,
                    $activity->module_name ?? 'N/A',
                    $activity->description,
                    $activity->ip_address ?? 'N/A',
                    ucfirst($activity->status),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clean old audit logs.
     */
    public function clean(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365'
        ]);

        $days = (int) $request->days;
        $cutoffDate = Carbon::now()->subDays($days);

        $deletedCount = AuditActivity::where('performed_at', '<', $cutoffDate)->delete();

        // Log this action
        $auditService = app(\App\Services\AuditService::class);
        $auditService->log(
            'deleted',
            'AuditActivity',
            null,
            'Bulk Cleanup',
            null,
            ['deleted_count' => $deletedCount, 'days' => $days],
            "Cleaned {$deletedCount} audit records older than {$days} days"
        );

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} audit records older than {$days} days.",
            'count' => $deletedCount
        ]);
    }

    /**
     * Get audit statistics.
     */
    private function getStatistics(Request $request): array
    {
        $query = AuditActivity::query();

        if ($request->filled('date_from')) {
            $query->whereDate('performed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('performed_at', '<=', $request->date_to);
        }

        // Total count
        $total = (clone $query)->count();

        // Today's count
        $today = AuditActivity::whereDate('performed_at', today())->count();

        // Success and failed counts
        $successCount = (clone $query)->where('status', 'success')->count();
        $failedCount = (clone $query)->where('status', 'failed')->count();

        // By action - Fixed
        $byAction = DB::table('audit_activities')
            ->select('action', DB::raw('count(*) as action_count'))
            ->when($request->filled('date_from'), function($q) use ($request) {
                return $q->whereDate('performed_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($q) use ($request) {
                return $q->whereDate('performed_at', '<=', $request->date_to);
            })
            ->groupBy('action')
            ->orderByDesc('action_count')
            ->get()
            ->pluck('action_count', 'action')
            ->toArray();

        // By module - Fixed
        $byModule = DB::table('audit_activities')
            ->select('module', DB::raw('count(*) as module_count'))
            ->when($request->filled('date_from'), function($q) use ($request) {
                return $q->whereDate('performed_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($q) use ($request) {
                return $q->whereDate('performed_at', '<=', $request->date_to);
            })
            ->groupBy('module')
            ->orderByDesc('module_count')
            ->get()
            ->pluck('module_count', 'module')
            ->toArray();

        // By user - Fixed
        $byUser = DB::table('audit_activities')
            ->select('user_name', DB::raw('count(*) as user_count'))
            ->whereNotNull('user_name')
            ->when($request->filled('date_from'), function($q) use ($request) {
                return $q->whereDate('performed_at', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function($q) use ($request) {
                return $q->whereDate('performed_at', '<=', $request->date_to);
            })
            ->groupBy('user_name')
            ->orderByDesc('user_count')
            ->limit(10)
            ->get()
            ->pluck('user_count', 'user_name')
            ->toArray();

        return [
            'total' => $total,
            'today' => $today,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'by_action' => $byAction,
            'by_module' => $byModule,
            'by_user' => $byUser,
        ];
    }
}
