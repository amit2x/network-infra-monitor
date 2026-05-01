<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;


class AlertController extends Controller
{
    // public static function middleware(): array
    // {
    //     return [
    //         'auth',
    //         new Middleware('permission:view alerts', only: ['index', 'show', 'getNotifications', 'getUnreadCount']),
    //         new Middleware('permission:resolve alerts', only: ['resolve', 'bulkResolve', 'markAsRead']),
    //     ];
    // }
    
      public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of alerts.
     */
    public function index(Request $request)
    {
        $query = Alert::with('device');

        // Apply filters
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Fix: Handle resolved filter properly
        if ($request->has('resolved') && $request->resolved !== '') {
            $query->where('is_resolved', $request->resolved == '1');
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Default: order by newest first
        $alerts = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20))
            ->withQueryString(); // Use withQueryString() to preserve filters in pagination

        // Get statistics for display
        $stats = [
            'total' => Alert::count(),
            'critical' => Alert::where('severity', 'critical')->where('is_resolved', false)->count(),
            'high' => Alert::where('severity', 'high')->where('is_resolved', false)->count(),
            'unresolved' => Alert::where('is_resolved', false)->count(),
            'today' => Alert::whereDate('created_at', today())->count(),
        ];

        return view('alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Display the specified alert.
     */
    public function show(Alert $alert)
    {
        $alert->load(['device.location', 'resolvedBy']);
        
        // Mark as read when viewed
        if (!$alert->is_read) {
            $alert->update(['is_read' => true]);
        }
        
        return view('alerts.show', compact('alert'));
    }

    /**
     * Mark alert as read.
     */
    public function markAsRead(Alert $alert)
    {
        $alert->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read.'
        ]);
    }

    /**
     * Resolve alert.
     */
    public function resolve(Alert $alert)
    {
        $alert->update([
            'is_resolved' => true,
            'is_read' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);

        // Log audit if Auditable trait is used
        if (method_exists($alert, 'audit')) {
            $alert->audit('resolved');
        }

        return response()->json([
            'success' => true,
            'message' => 'Alert resolved successfully.'
        ]);
    }

    /**
     * Bulk resolve alerts.
     */
    public function bulkResolve(Request $request)
    {
        $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'exists:alerts,id'
        ]);

        $count = count($request->alert_ids);

        Alert::whereIn('id', $request->alert_ids)->update([
            'is_resolved' => true,
            'is_read' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => "{$count} alerts resolved successfully."
        ]);
    }

    /**
     * Remove the specified alert.
     */
    public function destroy(Alert $alert)
    {
        try {
            $alert->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Alert deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete alert.'
            ], 500);
        }
    }

    /**
     * Get unread alerts count.
     */
    public function getUnreadCount()
    {
        $count = Alert::where('is_read', false)
            ->where('is_resolved', false)
            ->count();

        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Get recent notifications for dropdown.
     */
    public function getNotifications()
    {
        $alerts = Alert::with('device')
            ->where('is_resolved', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($alert) {
                return [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'severity' => $alert->severity,
                    'device_name' => $alert->device ? $alert->device->name : 'System',
                    'time' => $alert->created_at->diffForHumans(),
                    'is_read' => $alert->is_read,
                    'type' => $alert->type,
                ];
            });
        
        $unreadCount = Alert::where('is_read', false)
            ->where('is_resolved', false)
            ->count();
        
        return response()->json([
            'success' => true,
            'alerts' => $alerts,
            'unread_count' => $unreadCount
        ]);
    }
}